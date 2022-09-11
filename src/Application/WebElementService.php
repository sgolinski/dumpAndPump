<?php

namespace App\Application;

use App\Application\Validation\Allowed;
use App\Domain\ValueObjects\ExchangeChain;
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
        /*
         * 1. Jesli  type jest równy panckake i chain zawiera sie w exchange chains jest transakcja sprzedaży
         * 2. Jesli type panckake true i chain nie wskazuje na sprzedaż  buy transaction
         */

        foreach ($webElements as $webElement) {
            assert($webElement instanceof RemoteWebElement);

            $type = $this->factory->createTypeFrom($webElement);
            $chain = $this->factory->createExchangeChain($webElement);

            $isPancakeTransaction = $this->checkIfIsPancakeTransaction($type);
            $isSaleTransaction = $this->checkIfExchangePointSale($chain);

            if ($isPancakeTransaction && $isSaleTransaction) {
                $this->routerTransactionFactory->createTxnSaleTransaction($webElement, $chain);
            } elseif ($isPancakeTransaction && !$isSaleTransaction) {
                $this->routerTransactionFactory->createBuyTransaction($webElement);
            } else {
                $transaction = null;
            }
        }
    }

    private function checkIfExchangePointSale(ExchangeChain $chain): bool
    {
        if (in_array($chain, Allowed::EXCHANGE_CHAINS)) {
            return true;
        }
        return false;
    }

    private function checkIfIsPancakeTransaction(string $type): bool
    {
        return str_contains($type, 'PancakeSwap V2:');
    }
}