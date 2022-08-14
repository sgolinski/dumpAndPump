<?php

namespace App\Application;

use App\Application\Validation\Urls;
use App\Domain\Transaction;
use App\Domain\ValueObjects\Holders;
use App\Domain\ValueObjects\Url;
use App\Infrastructure\Repository\InMemoryRepository;
use App\Infrastructure\Repository\PantherRepository;
use App\Infrastructure\Repository\RedisRepository;
use DateTime;
use Exception;

class Application
{
    private InMemoryRepository $inMemoryRepository;

    private PantherRepository $pantherRepository;

    public RedisRepository $transactionRepository;

    public WebElementService $service;

    public function __construct()
    {
        $this->pantherRepository = new PantherRepository();
        $this->inMemoryRepository = new InMemoryRepository();
        $this->transactionRepository = new RedisRepository();
    }

    public function importAllTransactionsFromWebsite(
        int $from,
        int $till
    ): void
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

        $transactions = $this->pantherRepository->findElements($command->url());
        $imported = $this->service->transformElementsToTransactions($transactions);;

        foreach ($imported as $transaction) {
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
            $string = $this->pantherRepository->findOneElementOn($currentURl);
            $holders = Holders::fromString($string);

            if (!$holders->trustedHolders()) {
                $this->putTransactionOnBlacklist($transaction, $holders, $command);
                continue;
            }
            $this->putTransactionOnComplete($transaction, $command);
        }
    }

    private function putTransactionOnBlacklist(Transaction $transaction, Holders $holders, FillNotCompleteTransaction $command): void
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
}