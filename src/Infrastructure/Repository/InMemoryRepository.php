<?php

namespace App\Infrastructure\Repository;

use App\Application\Validation\Allowed;
use App\Application\Validation\Blacklisted;
use App\Application\Validation\Selectors;
use App\Domain\Transaction;
use App\Domain\ValueObjects\ExchangeChain;
use App\Domain\ValueObjects\Id;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use ArrayIterator;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use InvalidArgumentException;

class InMemoryRepository implements TransactionRepository
{
    private array $webElementCache = [];

    public function saveAllTransactions(ArrayIterator $webElements): array
    {
        foreach ($webElements as $cache) {
            try {
                $this->ensureCacheIsNotEmpty($cache);
                $dto = $this->createDTO($cache);
            } catch (Exception) {
                continue;
            }
            assert($dto instanceof Transaction);
            $key = $dto->id()->asString();
            if (empty($this->webElementCache) || !$this->hasId($key)) {
                $this->webElementCache[$key] = $dto;
                continue;
            }
            if ($this->webElementCache[$key]->price()->asFloat() === $dto->price()->asFloat()) {
                continue;
            }
            $this->webElementCache[$key]->noticeRepetitions();
        }
        return $this->webElementCache;
    }

    public function byId($id): Transaction
    {
        return $this->webElementCache[$id];
    }

    public function hasId(string $key): bool
    {

        if (empty($this->webElementCache)) {
            return false;
        }
        return array_key_exists($key, $this->webElementCache);
    }

    private function createNameFrom(RemoteWebElement $webElement): Name
    {

        return Name::fromString($webElement
            ->findElement(WebDriverBy::cssSelector(Selectors::FOR_NAME))
            ->getText());
    }

    private function createIdFrom(RemoteWebElement $webElement): Id
    {

        $id = Id::fromString($webElement
            ->findElement(WebDriverBy::cssSelector(Selectors::FOR_ADDRESS))
            ->getAttribute('href'));

        if (in_array($id->asString(), Blacklisted::ADDRESSES, true)) {
            throw new InvalidArgumentException('Blacklisted!');
        }

        return $id;
    }

    private function createPriceFrom(RemoteWebElement $webElement): Price
    {

        $information = $webElement
            ->findElement(WebDriverBy::cssSelector(Selectors::FOR_INFORMATION))
            ->getText();
        $information = explode(" ", $information);

        return $this->extractPriceFrom($information[0]);
    }

    private function createExchangeChain(RemoteWebElement $webElement): ExchangeChain
    {

        $information = $webElement
            ->findElement(WebDriverBy::cssSelector(Selectors::FOR_INFORMATION))
            ->getText();
        $information = explode(" ", $information);

        return $this->extractChainFrom($information[1]);
    }

    private function extractPriceFrom(
        string $float
    ): Price
    {
        $strPrice = str_replace([','], [''], $float);
        return Price::fromFloat(round((float)$strPrice, 3));
    }

    private function extractChainFrom(
        string $data
    ): ExchangeChain
    {
        return ExchangeChain::fromString(strtolower($data));
    }

    private function createDTO(RemoteWebElement $webElement): Transaction
    {
        try {
            return Transaction::writeNewFrom(
                $this->createIdFrom($webElement),
                $this->createNameFrom($webElement),
                $this->createPriceFrom($webElement),
                $this->createExchangeChain($webElement)

            );

        } catch (Exception) {
            throw new InvalidArgumentException();
        }
    }

    private function ensureCacheIsNotEmpty(mixed $cache)
    {
        if ($cache === null) {
            throw new InvalidArgumentException();
        }
    }

    public function all(): ?array
    {
        if (empty($this->webElementCache)) {
            return null;
        }
        return $this->webElementCache;
    }

    public function byRepetitions(): array
    {
        $repeated = [];
        foreach ($this->webElementCache as $transaction) {
            if (count($transaction->recordedEvents()) === 2) {
                $repeated[] = $transaction;
            }
        }
        return $repeated;
    }

    public function byPrice(): array
    {
        $drops = [];
        foreach ($this->webElementCache as $transaction) {
            if ($this->ensurePriceIsHighEnough($transaction->exchangeChain, $transaction->price)) {
                $drops[] = $transaction;
            }
        }
        return $drops;
    }


    private function ensurePriceIsHighEnough(
        ExchangeChain $chain,
        Price         $price
    ):bool
    {
        if ($price->asFloat() < Allowed::PRICE_PER_NAME[$chain->asString()]) {
            return false;
        }
        return true;
    }

    public function save(Transaction $transaction)
    {
        // TODO: Implement save() method.
    }
}
