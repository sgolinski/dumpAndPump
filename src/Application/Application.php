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
        $imported = $this->inMemoryRepository->saveAllTransactions($transactions);
        foreach ($imported as $transaction) {
            $this->transactionRepository->ensureHasNoStatus($transaction);
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
            $this->transactionRepository->save('notComplete', $dumpAndPumpTransaction);
        }
    }

    public function findBiggestTransactionDrops(): void
    {
        $this->findDropedTransactions(new FindBiggestDropTransactions());
    }

    private function findDropedTransactions(FindBiggestDropTransactions $command): void
    {
        $dropped = $this->inMemoryRepository->byPrice();

        foreach ($dropped as $transaction) {
            assert($transaction instanceof Transaction);
            $transaction->registerTransaction();
            $this->transactionRepository->save('notComplete', $transaction);
        }
    }

    public function assignHolders(): void
    {
        $this->findAllNotCompletedTransactions();
    }

    private function findAllNotCompletedTransactions(): void
    {
        $notCompleted = $this->transactionRepository->findAll('notComplete');

        foreach ($notCompleted as $transaction) {
            assert($transaction instanceof Transaction);
            $currentURl = Url::fromString(Urls::FOR_TRANSACTION . $transaction->id()->asString());
            $string = $this->pantherRepository->findOneElementOn($currentURl);
            $holders = Holders::fromString($string);

            if ($holders->asInt() < Holders::MIN_AMOUNT_HOLDERS) {
                $transaction->assignToBlackList($holders);
                $this->transactionRepository->save('blacklisted', $transaction);
                $this->transactionRepository->removeFrom('notComplete', $transaction);
                continue;
            }
            $transaction->completeTransaction();
            $this->transactionRepository->save('complete', $transaction);
            $this->transactionRepository->removeFrom('notComplete', $transaction);
        }
    }
}