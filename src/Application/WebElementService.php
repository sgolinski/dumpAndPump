<?php

namespace App\Application;

use App\Application\Validation\Allowed;
use App\Domain\ValueObjects\Name;
use App\Infrastructure\Repository\InMemoryRepository;
use App\Infrastructure\RouterTransactionFactory;
use Facebook\WebDriver\Remote\RemoteWebElement;

class WebElementService
{
    public RouterTransactionFactory $factory;
    public InMemoryRepository $repository;

    public function __construct(InMemoryRepository $repository)
    {
        $this->repository = $repository;
        $this->factory = new RouterTransactionFactory($this->repository);

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
            $tokenName = Name::fromString($tokenName);
            if ($isPancakeTransaction && $isSaleTransaction) {
                $transaction = $this->factory->createTxnSaleTransaction($webElement, $tokenName);

            } elseif (!$isPancakeTransaction && $isSaleTransaction) {
                $transaction = $this->factory->createTxnSaleTransaction($webElement, $tokenName);
                // var_dump($transaction);

            } elseif ($isPancakeTransaction && !$isSaleTransaction) {
                $transaction = $this->factory->createBuyTransaction($webElement);
                // var_dump($transaction);

            } else {
                $transaction = $this->factory->createBuyTransaction($webElement);
            }

            if ($transaction == null) {
                continue;
            }
        }

        var_dump($this->repository->all());
        die;
    }

    private function checkIfTokenNameIsExchangeTokenName(string $name): bool
    {
        if (in_array(strtolower($name), Allowed::EXCHANGE_CHAINS)) {
            return true;
        }
        return false;
    }

    private function checkIfIsPancakeTransaction(string $type): bool
    {
        foreach (Allowed::ROUTER_NAMES as $router_name) {
            if (str_contains(strtolower($type), strtolower($router_name))) {
                return true;
            }
        }
        return false;
    }
}