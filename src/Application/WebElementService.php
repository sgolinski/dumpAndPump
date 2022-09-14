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
            $type = $this->factory->createFromType($tokenName);
            $txnHash = $this->factory->createTxnHash($webElement);

            if ($type->asString() === 'exchange') {
                $isHighEnough = $this->ensurePriceIsHigherThenMinimum($price, $tokenName);
                if (!$isHighEnough) {
                    $this->repository->addToBlocked($txnHash);
                    continue;
                }
            }

            $columnToInformation = $this->factory->findToColumn($webElement);
            $columnFromInformation = $this->factory->findFromColumn($webElement);
            $fromTransactionTypePancake = $this->createTransactionTypePancake($columnFromInformation);
            $toTransactionTypePancake = $this->createTransactionTypePancake($columnToInformation);
            $fromTransactionTypeContract = $this->getFromTransactionTypeContract($fromTransactionTypePancake, $columnFromInformation, $webElement);
            $toTransactionTypeContract = $this->getToTransactionTypeContract($toTransactionTypePancake, $columnToInformation, $webElement);

           $isNotAllowed = $this->filterTransactions($fromTransactionTypePancake, $toTransactionTypePancake, $fromTransactionTypeContract, $toTransactionTypeContract, $txnHash);

            if ($isNotAllowed) {
                continue;
            }
            $this->createTransaction($type, $webElement, $tokenName, $price, $txnHash);
        }
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

    private function getFromTransactionTypeContract(?Type $toTransactionTypePancake, string $columnToInformation, RemoteWebElement $webElement): ?Type
    {
        if (!$toTransactionTypePancake) {
            if (str_contains($columnToInformation, '0x')) {
                return $this->factory->createFromTransactionTypeContract($webElement);
            }
            if (str_contains($columnToInformation, 'Null')) {
                return Type::fromString('null');
            }
        }
        return null;
    }

    private function getToTransactionTypeContract(?Type $toTransactionTypePancake, string $columnToInformation, RemoteWebElement $webElement): ?Type
    {
        if (!$toTransactionTypePancake) {
            if (str_contains($columnToInformation, '0x')) {
                return $this->factory->createToTransactionTypeContract($webElement);
            }
            if (str_contains($columnToInformation, 'Null')) {
                return Type::fromString('null');
            }
        }
        return null;
    }

    private function filterTransactions(?Type $fromTransactionTypePancake, ?Type $toTransactionTypePancake, ?Type $fromTransactionTypeContract, ?Type $toTransactionTypeContract, ?TxnHashId $txnHash): bool
    {
        //TODO FIND MORE CASES WHERE IS NOT NEEDED TO RECORD TRANSACTION
        if ($fromTransactionTypePancake == null && $toTransactionTypePancake == null
            && isset($fromTransactionTypeContract) && isset($toTransactionTypeContract)) {

            if ($fromTransactionTypeContract->asString() == 'null' && $toTransactionTypeContract->asString() == 'null') {
                $this->repository->addToBlocked($txnHash);
                return false;
            }
            if ($toTransactionTypeContract->asString() == 'privat' && $fromTransactionTypeContract->asString() == 'privat') {
                $this->repository->addToBlocked($txnHash);
                return false;
            }
        }
        return true;
    }

    private function createTransaction(Type $type, RemoteWebElement $webElement, Name $tokenName, Price $price, ?TxnHashId $txnHash): void
    {
        if ($type->asString() == 'exchange') {
            $this->factory->createTxnSaleTransaction($webElement, $tokenName, $price, $type, $txnHash);
        } elseif ($type->asString() == 'other') {
            $this->factory->createBuyTransaction($webElement, $type, $txnHash, $price, $tokenName);
        } else {
            $this->factory->createBuyTransaction($webElement, $type, $txnHash, $price, $tokenName);
        }
    }
}