<?php

namespace App\Application;

use App\Application\Validation\Allowed;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Infrastructure\Repository\InMemoryRepository;
use App\Infrastructure\RouterTransactionFactory;
use Facebook\WebDriver\Remote\RemoteWebElement;
use InvalidArgumentException;

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
            $price = $this->factory->createPriceFrom($webElement);

            $tokenName = $this->factory->createTokenName($webElement);
            $type = $this->factory->createType($tokenName);
            $isSaleTransaction = $this->checkIfTokenNameIsExchangeTokenName($tokenName->asString());

            try {

                if ($isSaleTransaction) {
                    $this->ensurePriceIsHigherThenMinimum($price, $tokenName);
                }
            } catch (InvalidArgumentException $exception) {
                continue;
            }

            $router = $this->factory->findRouterNameFrom($webElement);

            $isPancakeTransaction = $this->checkIfIsPancakeTransaction($router);

            if ($isPancakeTransaction && $isSaleTransaction) {
                $transaction = $this->factory->createTxnSaleTransaction($webElement, $tokenName, $price, $type);

            } elseif (!$isPancakeTransaction && $isSaleTransaction) {
                $transaction = $this->factory->createTxnSaleTransaction($webElement, $tokenName, $price, $type);


            } elseif ($isPancakeTransaction && !$isSaleTransaction) {
                $transaction = $this->factory->createBuyTransaction($webElement, $type);

            } else {
                $transaction = $this->factory->createBuyTransaction($webElement, $type);
            }

            if ($transaction == null) {
                continue;
            }
        }
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

    private function ensurePriceIsHigherThenMinimum(Price $price, Name $tokenName): void
    {
        if ($price->asFloat() <= Allowed::MIN_PRICE_PER_NAME[$tokenName->asString()]) {
            throw new InvalidArgumentException();
        }
    }
}