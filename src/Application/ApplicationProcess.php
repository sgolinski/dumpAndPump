<?php

namespace App\Application;

class ApplicationProcess
{
    private Application $application;

    public function __construct()
    {
        $this->application = new Application();
    }

    public function invoke(): void
    {
        $this->application->importAllTransactionsFromWebsite(1, 100);
    }

    public function processEvents(): void
    {
        $this->application->findRepeated();
        $this->application->findBiggestTransactionDrops();
//        $this->application->completeTransaction();
//        $this->application->transactionRepository->size();
    }
}