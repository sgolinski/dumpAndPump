<?php

namespace App\Application;

use App\Application\Validation\Urls;
use App\Domain\Transaction;
use App\Domain\ValueObjects\Holders;
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

    public function noteRepeatedTransactions(): void
    {
        $this->findRepeatedTransactions(new FindPotentialDumpAndPumpTransaction());
    }

    private function findRepeatedTransactions(FindPotentialDumpAndPumpTransaction $command): void
    {
        $potentialDumpAndPumpTransactions = $this->inMemoryRepository->byRepetitions();

        foreach ($potentialDumpAndPumpTransactions as $potentialDumpAndPumpTransaction) {
            assert($potentialDumpAndPumpTransaction instanceof Transaction);
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
        $saleTransactions = $this->inMemoryRepository->byPrice();
        foreach ($saleTransactions as $saleTransaction) {
            assert($saleTransaction instanceof Transaction);
            $saleTransaction->registerTransaction();
            $this->transactionRepository->save($command->notComplete(), $saleTransaction);
        }
    }

    public function completeTransaction(): void
    {
        $this->fillAllNotCompletedTransactions(new FillNotCompleteTransaction());
    }

    private function fillAllNotCompletedTransactions(FillNotCompleteTransaction $command): void
    {
        $notCompleteTransactions = $this->transactionRepository->findAll($command->notComplete());

        foreach ($notCompleteTransactions as $notCompletedTransaction) {
            assert($notCompletedTransaction instanceof Transaction);

            $currentURl = Url::fromString(Urls::FOR_TRANSACTION . $notCompletedTransaction->id()->asString());
            $elementFrom = $this->pantherService->findOneElementOn($currentURl);
            $holdersAmount = Holders::fromString($elementFrom);

            if ($holdersAmount->enoughToTrust()) {
                $this->putTransactionOnComplete($notCompletedTransaction, $command);
                continue;
            }
            $this->putTransactionOnBlacklist($notCompletedTransaction, $holdersAmount, $command);
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
}