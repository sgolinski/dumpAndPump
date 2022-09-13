<?php

namespace App\Application;

use DateTime;

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
            //$this->application->noteRepeatedTransactions();
            $this->application->findBiggestTransactionDrops();
        }

    }

    public function processEvents(): void
    {
        $now = DateTime::createFromFormat('U.u', microtime(true));
        echo 'Start processing events ' . $now->format("m-d-Y H:i:s.u") . PHP_EOL;
        $this->application->filterNotListed();
        $this->application->completeTransaction();
        $this->application->sendNotifications();
        $this->application->transactionRepository->saveDb();
        $now = DateTime::createFromFormat('U.u', microtime(true));
        echo 'Process finished ' . $now->format("m-d-Y H:i:s.u") . PHP_EOL;
    }
}