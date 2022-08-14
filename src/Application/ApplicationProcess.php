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
            $this->application->findRepeated();
            $this->application->findBiggestTransactionDrops();
        }
    }

    public function processEvents(): void
    {
        $this->application->completeTransaction();
        $this->application->sendNotifications();
        $this->application->transactionRepository->size();
    }
}