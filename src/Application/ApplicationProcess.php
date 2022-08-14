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
            $this->application->importAllTransactionsFromWebsite($i, $end);
            $this->application->findRepeated();
            $this->application->findBiggestTransactionDrops();
        }
    }

    public function processEvents(): void
    {
//        $this->application->findRepeated();
//        $this->application->findBiggestTransactionDrops();
        $this->application->completeTransaction();
        $this->application->transactionRepository->size();
    }
}