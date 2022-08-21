<?php

namespace App\Application;

class ApplicationProcess
{
    private Application $application;

    public function __construct()
    {
        $this->application = new Application();
    }

    public function invoke(int $start, int $end): void
    {
        for ($i = $start; $i < $end; $i++) {
            $this->application->importAllTransactionsFromWebsite($i);
            $this->application->noteRepeatedTransactions();
            $this->application->findBiggestTransactionDrops();
        }
        $this->application->transactionRepository->saveDb();
    }

    public function processEvents(): void
    {
        $this->application->completeTransaction();
        $this->application->sendNotifications();
        $this->application->transactionRepository->saveDb();
    }
}