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
    }

    public function importAllTransactionsFromWebsite(int $from): void
    {
        try {
            $this->importTransactions(new ImportTransaction($from));
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }

    private function importTransactions(ImportTransaction $command): void
    {
        $this->service = new WebElementService($this->inMemoryRepository);

        $now = DateTime::createFromFormat('U.u', microtime(true));
        echo $command->url()->asString() . ' ' . $now->format("m-d-Y H:i:s.u") . PHP_EOL;

        try {
            $this->pantherService->saveWebElements($command->url());
        } catch (Exception) {
            $this->pantherService->getClient()->close();
            $this->pantherService->getClient()->quit();
        }

        $importedTransactions = $this->service->transformElementsToTransactions($this->pantherService->savedWebElements());

        foreach ($importedTransactions as $transaction) {
            if (!$this->transactionRepository->ensureHasAllowedStatus($transaction)) {
                $this->inMemoryRepository->remove($transaction->id()->asString());
            }
        }
    }

    public function findRepeated(): void
    {
        $this->findRepeatedTransactions(new FindDumpAndPumpTransaction());
    }

    private function findRepeatedTransactions(FindDumpAndPumpTransaction $command): void
    {
        $potentialDumpAndPumpTransactions = $this->inMemoryRepository->byRepetitions();

        foreach ($potentialDumpAndPumpTransactions as $dumpAndPumpTransaction) {
            assert($dumpAndPumpTransaction instanceof Transaction);
            $dumpAndPumpTransaction->pumpAndDumpRecognized();
            $this->transactionRepository->save($command->status(), $dumpAndPumpTransaction);
        }
    }

    public function findBiggestTransactionDrops(): void
    {

        $this->findDroppedTransactions(new FindBiggestDropTransactions());
    }

    private function findDroppedTransactions(FindBiggestDropTransactions $command): void
    {
        $dropped = $this->inMemoryRepository->byPrice();
        foreach ($dropped as $transaction) {
            assert($transaction instanceof Transaction);
            $transaction->registerTransaction();
            $this->transactionRepository->save($command->status(), $transaction);
        }
    }

    public function completeTransaction(): void
    {
        $this->fillAllNotCompletedTransactions(new FillNotCompleteTransaction());
    }

    private function fillAllNotCompletedTransactions(FillNotCompleteTransaction $command): void
    {
        $notCompleted = $this->transactionRepository->findAll($command->notComplete());

        foreach ($notCompleted as $transaction) {
            assert($transaction instanceof Transaction);

            $currentURl = Url::fromString(Urls::FOR_TRANSACTION . $transaction->id()->asString());
            $string = $this->pantherService->findOneElementOn($currentURl);
            $holders = Holders::fromString($string);

            if ($holders->trustedHolders()) {
                $this->putTransactionOnComplete($transaction, $command);
                continue;
            }
            $this->putTransactionOnBlacklist($transaction, $holders, $command);
        }
    }

    private function putTransactionOnBlacklist(
        Transaction                $transaction,
        Holders                    $holders,
        FillNotCompleteTransaction $command
    ): void
    {
        $transaction->assignToBlackList($holders);
        $this->transactionRepository->save($command->blacklist(), $transaction);
        $this->transactionRepository->removeFrom($command->notComplete(), $transaction);
    }

    private function putTransactionOnComplete(Transaction $transaction, FillNotCompleteTransaction $command): void
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
        $completed = $this->transactionRepository->findAll($command->complete());
        $this->notificationService->sendNotificationsFor($completed);
        foreach ($completed as $completeTransaction) {
            $this->transactionRepository->save($command->sent(), $completeTransaction);
            $this->transactionRepository->removeFrom($command->complete(), $completeTransaction);
            $completeTransaction->sendTransaction();
        }
    }
}