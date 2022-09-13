<?php

namespace App\Application;

use App\Application\Validation\Allowed;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\TxnHashId;
use App\Domain\ValueObjects\Type;
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
            $transactionType = $this->factory->createType($tokenName);
            $txnHash = $this->factory->createTxnHash($webElement);

            if ($transactionType->asString() === 'exchange') {
                $isHighEnough = $this->ensurePriceIsHigherThenMinimum($price, $tokenName);
                if ($isHighEnough) {
                    $this->repository->removeFromBlocked($txnHash);
                } else {
                    $this->repository->addToBlocked($txnHash);
                    continue;
                }
            }

            $router = $this->factory->findRouterNameFrom($webElement);
            $transactionTypePancake = $this->createTransactionTypePancake($router);

            if (!$transactionTypePancake) {
                $contractType = $this->factory->createTransactionTypeContract($webElement);
            }

            if ($transactionType->asString() == 'exchange') {
                $transaction = $this->factory->createTxnSaleTransaction($webElement, $tokenName, $price, $transactionType, $txnHash);

            } elseif ($transactionType->asString() == 'other') {
                $transaction = $this->factory->createBuyTransaction($webElement, $transactionType, $txnHash, $price, $tokenName);

            } else {
                $transaction = $this->factory->createBuyTransaction($webElement, $transactionType, $txnHash, $price, $tokenName);
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

    private function createTransactionTypePancake(string $type): ?Type
    {
        foreach (Allowed::ROUTER_NAMES as $router_name) {
            if (str_contains(strtolower($type), strtolower($router_name))) {
                return Type::fromString('pancake');
            }
        }
        return null;
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