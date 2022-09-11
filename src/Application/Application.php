<?php

namespace App\Application;

use App\Application\Validation\Urls;
use App\Domain\BuyTransaction;
use App\Domain\Event\FindPotentialDumpAndPumpTransaction;
use App\Domain\Transaction;
use App\Domain\TransactionInterface;
use App\Domain\TxnSaleTransaction;
use App\Domain\ValueObjects\Url;
use App\Infrastructure\Repository\InMemoryRepository;

use App\Infrastructure\Repository\RedisRepository;
use DateTime;
use Exception;

class Application
{
    private InMemoryRepository $inMemoryRepository;

    private PantherService $pantherService;

    public RedisRepository $transactionRepository;

    public WebElementService $service;

    public NotificationService $notificationService;

    public function __construct()
    {
        $this->pantherService = new PantherService();
        $this->inMemoryRepository = new InMemoryRepository();
        $this->transactionRepository = new RedisRepository();
        $this->notificationService = new NotificationService();
        $this->service = new WebElementService($this->inMemoryRepository);
    }

    public function importAllTransactionsFromWebsite(int $number): void
    {
        try {
            $this->importTransactionsFrom(new ImportTransaction($number));
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }

    private function importTransactionsFrom(ImportTransaction $command): void
    {
        $now = DateTime::createFromFormat('U.u', microtime(true));
        echo $command->url()->asString() . ' ' . $now->format("m-d-Y H:i:s.u") . PHP_EOL;

        $this->pantherService->saveWebElements($command->url());
        $this->service->transformElementsToTransactions($this->pantherService->savedWebElements());
    }

    public function noteRepeatedSaleTransactions(): void
    {
        $this->findRepeatedSaleTransactions(new FindPotentialDumpAndPumpTransaction());
    }

    private function findRepeatedSaleTransactions(FindPotentialDumpAndPumpTransaction $command): void
    {
        $potentialDumpAndPumpTransactions = $this->inMemoryRepository->byRepetitions();

        foreach ($potentialDumpAndPumpTransactions as $potentialDumpAndPumpTransaction) {
            assert($potentialDumpAndPumpTransaction instanceof BuyTransaction);
            $potentialDumpAndPumpTransaction->recognizePumpAndDump();
            $this->transactionRepository->save($command->notComplete(), $potentialDumpAndPumpTransaction);
        }
    }

    public function findBiggestTransactionDrops(): void
    {
        $this->filterSaleTransactions(new FindBiggestSaleTransaction());
    }

    private function filterSaleTransactions(FindBiggestSaleTransaction $command): void
    {
        $transactions = $this->inMemoryRepository->all();
        $currentPrice = 0.0;
        foreach ($transactions as $transactionArr) {

            foreach ($transactionArr as $transaction) {
                assert($transaction instanceof TransactionInterface);
                if ($transaction->type() == 'exchange' && $transaction > $currentPrice) {
                    $exchangeName = $transaction->name();
                    $exchangePrice = $transaction->price();
                    $txnHash = $transaction->id();
                    $currentPrice = $transaction->price()->asFloat();
                } else {
                    $id = $transaction->id();
                    $name = $transaction->name();
                }
            }

            $transaction = Transaction::writeNewFrom($id, $name, $exchangePrice, $exchangeName, $txnHash);
            $this->transactionRepository->save($command->notComplete(), $transaction);
        }

    }

    public function completeTransaction(): void
    {
        $this->completeListedTransactions(new FillNotCompleteTransaction());
    }

    private function completeListedTransactions(FillNotCompleteTransaction $command): void
    {
        $listedTransactions = $this->transactionRepository->findAll($command->listed());

        foreach ($listedTransactions as $listedTransaction) {
            assert($listedTransaction instanceof TxnSaleTransaction);

            $currentURl = Url::fromString(Urls::FOR_TRANSACTION . $listedTransaction->id()->asString());
            $elementFrom = $this->pantherService->findOneElementOn($currentURl);
            $holdersAmount = Holders::fromString($elementFrom);

            if ($holdersAmount->enoughToTrust()) {
                $this->putTransactionOnComplete($listedTransaction, $command);
                continue;
            }
            $this->putTransactionOnBlacklist($listedTransaction, $holdersAmount, $command);
        }
    }

    private function putTransactionOnBlacklist(
        Transaction                $transaction,
        Holders                    $holders,
        FillNotCompleteTransaction $command
    ): void
    {
        $transaction->putOnBlacklist($holders);
        $this->transactionRepository->save($command->blacklist(), $transaction);
        $this->transactionRepository->removeFrom($command->notComplete(), $transaction);
    }

    private function putTransactionOnComplete(
        Transaction                $transaction,
        FillNotCompleteTransaction $command
    ): void
    {
        $transaction->completeTransaction();
        $this->transactionRepository->save($command->complete(), $transaction);
        $this->transactionRepository->removeFrom($command->notComplete(), $transaction);
    }

    public function sendNotifications(): void
    {
        $this->sendNotificationsAboutCompleteTokens(new NotifyOnSlack());
    }

    private function sendNotificationsAboutCompleteTokens(NotifyOnSlack $command): void
    {
        $completeTransactions = $this->transactionRepository->findAll($command->complete());
        $this->notificationService->sendNotificationsFor($completeTransactions);
        foreach ($completeTransactions as $completeTransaction) {
            $this->transactionRepository->save($command->sent(), $completeTransaction);
            $this->transactionRepository->removeFrom($command->complete(), $completeTransaction);
            $completeTransaction->sendNotification();
        }
    }

    public function filterNotListed(): void
    {
        $this->filterAllNotCompletedTransactions(new FindListedTransaction());
    }

    private function filterAllNotCompletedTransactions(FindListedTransaction $command): void
    {
        $notCompleteTransactions = $this->transactionRepository->findAll($command->notComplete());

        foreach ($notCompleteTransactions as $notCompletedTransaction) {
            assert($notCompletedTransaction instanceof Transaction);

            $currentURl = Url::fromString(Urls::FOR_LISTED . $notCompletedTransaction->id()->asString());
            $status = $this->pantherService->findAttributeElementOn($currentURl);

            switch ($status) {

                case 'show':
                    $notCompletedTransaction->putTransactionOnListed($notCompletedTransaction);
                    $this->transactionRepository->save($command->listed(), $notCompletedTransaction);
                    break;
                case 'not_found':
                    $notCompletedTransaction->putTransactionOnNotListed($notCompletedTransaction);
                    $this->transactionRepository->save($command->notListed(), $notCompletedTransaction);
                    break;
            }
            $this->transactionRepository->removeFrom($command->notComplete(), $notCompletedTransaction);
        }
    }
}