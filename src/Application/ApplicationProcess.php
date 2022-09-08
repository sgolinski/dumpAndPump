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
        for ($i = 0; $i < ($end * 2); $i += 2) {
            $this->application->importAllTransactionsFromWebsite($i);
//            $this->application->noteRepeatedTransactions();
//            $this->application->findBiggestTransactionDrops();
        }

    }

    public function processEvents(): void
    {
//        $this->application->filterNotListed();
//        $this->application->completeTransaction();
//        $this->application->sendNotifications();
        $this->application->transactionRepository->saveDb();
    }
}