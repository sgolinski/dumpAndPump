<?php

namespace App\Application;

use App\Infrastructure\Factory;
use App\Infrastructure\LiquidityTransactionFactory;
use App\Infrastructure\Repository\InMemoryRepository;
use App\Infrastructure\RouterTransactionFactory;
use App\Infrastructure\TransactionFactory;
use Facebook\WebDriver\Remote\RemoteWebElement;

class WebElementService
{
    public Factory $factory;
    public TransactionFactory $transactionFactory;
    public RouterTransactionFactory $routerTransactionFactory;
    public LiquidityTransactionFactory $liquidityTransactionFactory;
    public InMemoryRepository $repository;

    public function __construct(InMemoryRepository $repository)
    {
        $this->factory = new Factory();
        $this->transactionFactory = new TransactionFactory();
        $this->routerTransactionFactory = new RouterTransactionFactory();
        $this->liquidityTransactionFactory = new LiquidityTransactionFactory();
        $this->repository = $repository;
    }

    public function transformElementsToTransactions(array $webElements): void
    {
        foreach ($webElements as $webElement) {
            assert($webElement instanceof RemoteWebElement);


            $type = $this->factory->createTypeFrom($webElement);
            switch ($type) {
                case str_contains($type, 'PancakeSwap V2:'):
                    $this->routerTransactionFactory->createTransaction($webElement);
                    break;
                case str_contains($type, '0x6f42895f37291ec45f0a307b155229b923ff83f1'):
                    break;
//                case str_contains($type, '0x0ed943ce24baebf257488771759f9bf482c39706'):
//                    //  $this->liquidityTransactionFactory->createTransaction($webElement);
//                    break;
//                case str_contains($type, 'PancakeSwap:'):
//                    break;

//                case str_contains($type, 'PancakeSwap V2: BSC-USD-'):
//                    $this->routerTransactionFactory->createTransaction($webElement);
//                    break;
//                case str_contains($type, 'Null'):
//                    //  $this->transactionFactory->createTransaction($webElement);
//                    break;
//                case str_contains($type, '0x05ad60d9a2f1aa30ba0cdbaf1e0a0a145fbea16f'):
//                    break;


            }
        }
    }
}