<?php

namespace App\Application;

use App\Application\Validation\Allowed;
use App\Domain\Transaction;
use App\Domain\ValueObjects\Name;
use App\Infrastructure\Factory;
use App\Infrastructure\LiquidityTransactionFactory;
use App\Infrastructure\Repository\InMemoryRepository;
use App\Infrastructure\Repository\InMemorySaleTransactionRepository;
use App\Infrastructure\RouterTransactionFactory;
use App\Infrastructure\TransactionFactory;
use Facebook\WebDriver\Remote\RemoteWebElement;

class WebElementService
{
    public RouterTransactionFactory $factory;
    public InMemoryRepository $repository;
    public InMemorySaleTransactionRepository $saleTransactionRepository;

    public function __construct(InMemoryRepository $repository, InMemorySaleTransactionRepository $saleTransactionRepository)
    {
        $this->factory = new RouterTransactionFactory();
        $this->repository = $repository;
        $this->saleTransactionRepository = $saleTransactionRepository;
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
            $tokenName = $this->factory->createTokenName($webElement);

            $isPancakeTransaction = $this->checkIfIsPancakeTransaction($type);
            $isSaleTransaction = $this->checkIfTokenNameIsExchangeTokenName($tokenName);

            if ($isPancakeTransaction && $isSaleTransaction) {
                $transaction = $this->factory->createTxnSaleTransaction($webElement, $tokenName);

                var_dump($transaction);
                die;
                $key = $transaction->id()->asString();
                if ($this->saleTransactionRepository->isEmpty() || $this->saleTransactionRepository->hasId($key)) {
                    $this->repository->add($key, $transaction);
                    continue;
                }

            } elseif (!$isPancakeTransaction && $isSaleTransaction) {
                $transaction = $this->factory->createTxnSaleTransaction($webElement, $tokenName);
                var_dump($transaction);
                die;
            } elseif ($isPancakeTransaction && !$isSaleTransaction) {
                $transaction = $this->factory->createBuyTransaction($webElement);
                var_dump($transaction);
                die;
            } else {
                $transaction = null;
            }

            if ($transaction == null) {
                continue;
            }
        }
    }

    private function checkIfTokenNameIsExchangeTokenName(Name $chain): bool
    {
        if (in_array($chain, Allowed::EXCHANGE_CHAINS)) {
            return true;
        }
        return false;
    }

    private function checkIfIsPancakeTransaction(string $type): bool
    {
        return str_contains(strtolower($type), strtolower('PancakeSwap V2:'));
    }
}