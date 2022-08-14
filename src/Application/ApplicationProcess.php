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
        $this->application->importAllTransactionsFromWebsite(1, 2);
        $this->application->findRepeated();
        $this->application->findBiggestTransactionDrops();
        $this->application->assignHolders();
        $this->application->transactionRepository->size();
    }

    public function processEvents(): void
    {
        $this->application->assignHolders();
        $this->application->transactionRepository->size();
    }
}