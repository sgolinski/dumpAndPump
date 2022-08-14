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
        $this->service = new WebElementService();
    }

    public function importAllTransactionsFromWebsite(
        int $from,
        int $till
    ): void
    {
        try {
            $this->importTransactions(new ImportTransaction($from, $till));
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }

    private function importTransactions(ImportTransaction $command): void
    {
        $currentUrl = Url::fromString(Urls::FOR_COMMAND . $command->startPage());
        $now = DateTime::createFromFormat('U.u', microtime(true));
        echo $currentUrl->asString() . ' ' . $now->format("m-d-Y H:i:s.u") . PHP_EOL;
        $transactions = $this->pantherRepository->findElements($currentUrl);
        $imported = $this->service->transformElementsToTransactions($transactions);
        $endPage = $command->endPage();
        foreach ($imported as $transaction) {
            $this->transactionRepository->ensureHasNoStatus($transaction);
        }
    }

    // lapie czerowna linie na podstawie kilku transakcji np 5 malych ale daje sume jednej wiekszej
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
            $this->transactionRepository->save('notComplete', $dumpAndPumpTransaction);
        }
    }

    public function findBiggestTransactionDrops(): void
    {
        $this->findDroppedTransactions(new FindBiggestDropTransactions());
    }

    private function findDroppedTransactions(FindBiggestDropTransactions $command): void
    {
        $dropped = $this->inMemoryRepository->byPrice();
// status
        foreach ($dropped as $transaction) {
            assert($transaction instanceof Transaction);
            $transaction->registerTransaction();
            $this->transactionRepository->save('notComplete', $transaction);
        }
    }

    public function completeTransaction(): void
    {
        $this->findAllNotCompletedTransactions();
    }

    private function findAllNotCompletedTransactions(): void
    {
        $notCompleted = $this->transactionRepository->findAll('notComplete');

        foreach ($notCompleted as $transaction) {
            assert($transaction instanceof Transaction);
            // url komenda
            $currentURl = Url::fromString(Urls::FOR_TRANSACTION . $transaction->id()->asString());
            // mega ważna walidacja
            $string = $this->pantherRepository->findOneElementOn($currentURl);
            $holders = Holders::fromString($string);

            if (!$holders->trustedHolders()) {
                $this->putTransactionOnBlacklist($transaction, $holders);
                continue;
            }
            $this->putTransactionOnComplete($transaction);
        }
    }

    private function putTransactionOnBlacklist(Transaction $transaction, Holders $holders): void
    {
        $transaction->assignToBlackList($holders);
        $this->transactionRepository->save('blacklisted', $transaction);
        $this->transactionRepository->removeFrom('notComplete', $transaction);
    }

    private function putTransactionOnComplete(Transaction $transaction): void
    {
        $transaction->completeTransaction();
        $this->transactionRepository->save('complete', $transaction);
        $this->transactionRepository->removeFrom('notComplete', $transaction);
    }
}