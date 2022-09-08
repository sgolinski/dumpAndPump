<?php

namespace App\Infrastructure;

use App\Application\Validation\Allowed;
use App\Application\Validation\Blacklisted;
use App\Application\Validation\RouterSelectors;
use App\Domain\BuyTransaction;
use App\Domain\TxnSaleTransaction;
use App\Domain\TransactionInterface;
use App\Domain\ValueObjects\ExchangeChain;
use App\Domain\ValueObjects\Id;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Infrastructure\Repository\InMemoryRepository;
use App\Infrastructure\Repository\InMemorySaleTransactionRepository;
use App\Infrastructure\Repository\RedisRepository;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;

class RouterTransactionFactory
{
    private RedisRepository $repository;
    private InMemoryRepository $inMemoryRepository;
    private InMemorySaleTransactionRepository $inMemorySaleTransactionRepository;

    public function __construct()
    {
        $this->repository = new RedisRepository();
        $this->inMemoryRepository = new InMemoryRepository();
        $this->inMemorySaleTransactionRepository = new InMemorySaleTransactionRepository();
    }

    public function createTransaction(RemoteWebElement $webElement): void
    {
        $chain = $this->createExchangeChain($webElement);
        echo $chain . PHP_EOL;

        if (in_array($chain, Allowed::ADDRESSES)) {
            $transaction = $this->createTxnSaleTransaction($webElement, $chain);
            $this->inMemorySaleTransactionRepository->add($transaction->id()->asString(), $transaction);
        } else {
            $transaction = $this->createBuyTransaction($webElement);
            $this->inMemoryRepository->add($transaction->id()->asString(), $transaction);
        }
    }

    public function createAddressFrom(RemoteWebElement $webElement): string
    {
        return $webElement
            ->findElement(WebDriverBy::cssSelector(RouterSelectors::FOR_NAME))
            ->getText();
    }

    private function createPriceFrom(RemoteWebElement $webElement): string
    {
        return $webElement
            ->findElement(WebDriverBy::cssSelector(RouterSelectors::FOR_PRICE))
            ->getText();
    }

    private function createExchangeChain(RemoteWebElement $webElement): string
    {
        $information = $webElement
            ->findElement(WebDriverBy::cssSelector(RouterSelectors::FOR_CHAIN))
            ->getAttribute('href');

        return str_replace('/token/', '', $information);
    }

    private function createExchangeChainName(RemoteWebElement $webElement): string
    {
        $information = $webElement
            ->findElement(WebDriverBy::cssSelector(RouterSelectors::FOR_CHAIN))
            ->getText();

        $str = strstr($information, "(");
        $chain = str_replace(['(', ')'], '', $str);

        return strtolower($chain);
    }

    private function createTxn(RemoteWebElement $webElement): ?string
    {
        return $webElement
            ->findElement(WebDriverBy::cssSelector(RouterSelectors::HASH_TXN))
            ->getAttribute('href');
    }

    private function createTxnSaleTransaction(
        RemoteWebElement $webElement,
        string           $chain
    ): TransactionInterface
    {
        $highPrice = false;
        $chainName = $this->createExchangeChainName($webElement);
        $chainName = Name::fromString($chainName);
        $price = $this->createPriceFrom($webElement);
        $price = Price::fromFloat($price);
        if ($price->asFloat() >= Allowed::PRICE_PER_NAME[$chainName->asString()]) {
            $highPrice = true;
        }
        $hash = $this->createTxn($webElement);
        $chain = ExchangeChain::fromString($chain);
        $id = Id::fromString(str_replace('/tx/', '', $hash));
        return TxnSaleTransaction::writeNewFrom($id, $chainName, $chain, $price, $highPrice);
    }

    private function createBuyTransaction(
        RemoteWebElement $webElement
    ): BuyTransaction
    {
        $hash = $this->createTxn($webElement);
        $address = $this->createAddressFrom($webElement);
        $address = ExchangeChain::fromString($address);
        $id = $this->createExchangeChain($webElement);
        $id = Id::fromString($id);
        $name = $this->createExchangeChainName($webElement);
        $name = Name::fromString($name);
        $price = $this->createPriceFrom($webElement);
        $price = Price::fromFloat((float)$price);
        return BuyTransaction::writeNewFrom($id, $name, $address, $price);
    }

    public function findSoldTokens($webElement, int $count): ?string
    {
        for ($i = 1; $i <= $count; $i++) {
            $selector = RouterSelectors::FOR_SOLD_TOKEN_CON_START . $i . RouterSelectors::FOR_SOLD_TOKEN_CON_END;
            $token = $this->createNameFromTxn($webElement, $selector);
            if (!$this->ensureIsToken($token)) {
                return $this->createAddressFromTxn($webElement, $selector);
            }
        }
        return null;
    }

    private function createNameFromTxn(
        RemoteWebElement $webElement,
        string           $selector
    ): ?string
    {
        return $webElement
            ->findElement(WebDriverBy::cssSelector($selector))
            ->getText();
    }

    private function createAddressFromTxn(
        RemoteWebElement $webElement,
        string           $selector
    ): ?string
    {
        return $webElement
            ->findElement(WebDriverBy::cssSelector($selector))
            ->getAttribute('href');
    }

    private function ensureIsToken(
        string $token
    ): bool
    {
        $check = false;
        foreach (Blacklisted::TXN_TOKENS_NAMES as $blacklisted) {
            if (str_contains($blacklisted, $token)) {
                $check = true;
            }
        }
        return $check;
    }
}