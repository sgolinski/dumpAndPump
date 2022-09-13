<?php

namespace App\Application;

use App\Application\Validation\Allowed;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\TxnHashId;
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

        foreach ($webElements as $webElement) {

            assert($webElement instanceof RemoteWebElement);

            $price = $this->factory->createPriceFrom($webElement);
            $tokenName = $this->factory->createTokenName($webElement);
            $type = $this->factory->createType($tokenName);
            $isSaleTransaction = $this->checkIfTokenNameIsExchangeTokenName($tokenName->asString());
            $txnHash = $this->factory->createTxnHash($webElement);

            if ($isSaleTransaction) {
                $isHighEnough = $this->ensurePriceIsHigherThenMinimum($price, $tokenName);
                if ($isHighEnough) {
                    $this->repository->removeFromBlocked($txnHash);
                } else {
                    $this->repository->addToBlocked($txnHash);
                    continue;
                }
            }
            $router = $this->factory->findRouterNameFrom($webElement);
            $isPancakeTransaction = $this->checkIfIsPancakeTransaction($router);

            if ($isPancakeTransaction && $isSaleTransaction) {
                $transaction = $this->factory->createTxnSaleTransaction($webElement, $tokenName, $price, $type, $txnHash);

            } elseif (!$isPancakeTransaction && $isSaleTransaction) {
                $transaction = $this->factory->createTxnSaleTransaction($webElement, $tokenName, $price, $type, $txnHash);


            } elseif ($isPancakeTransaction && !$isSaleTransaction) {
                $transaction = $this->factory->createBuyTransaction($webElement, $type, $txnHash);

            } else {
                $transaction = $this->factory->createBuyTransaction($webElement, $type, $txnHash);
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

    private function ensurePriceIsHigherThenMinimum(Price $price, Name $tokenName): bool
    {
        if ($tokenName->ensureNameIsAllowed()) {
            if ($price->asFloat() <= Allowed::PRICE_PER_NAME[$tokenName->asString()]) {
                return false;
            }
            return true;
        }
        return false;
    }
}