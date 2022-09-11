<?php

namespace App\Infrastructure;

use App\Application\Validation\Allowed;
use App\Application\Validation\Blacklisted;
use App\Application\Validation\RouterSelectors;
use App\Domain\BuyTransaction;
use App\Domain\TxnSaleTransaction;
use App\Domain\ValueObjects\Address;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Id;
use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\TxnHashId;
use App\Infrastructure\Repository\InMemoryRepository;
use App\Infrastructure\Repository\RedisRepository;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;

class RouterTransactionFactory
{
    private RedisRepository $repository;
    private InMemoryRepository $inMemoryRepository;


    public function __construct(InMemoryRepository $inMemoryRepository)
    {
        $this->repository = new RedisRepository();
        $this->inMemoryRepository = $inMemoryRepository;
    }

    public function createTxnSaleTransaction(
        RemoteWebElement $webElement,
        Name             $tokenName
    ): TxnSaleTransaction
    {
        $txnHash = $this->createTxnHash($webElement);
        $txnHashId = TxnHashId::fromString(str_replace('/tx/', '', $txnHash));

        $tokenAddress = $this->createToken($webElement);
        $tokenAddress = Address::fromString($tokenAddress);

        $price = $this->createPriceFrom($webElement);
        $price = Price::fromFloat($price);

        $highPrice = false;
        if ($price->asFloat() >= Allowed::PRICE_PER_NAME[$tokenName->asString()]) {
            $highPrice = true;
        }

        $transaction = TxnSaleTransaction::writeNewFrom($txnHashId, $tokenName, $tokenAddress, $price, $highPrice);

        $this->inMemoryRepository->add($transaction->id()->asString(), $transaction);

        return $transaction;
    }

    public function createBuyTransaction(
        RemoteWebElement $webElement
    ): BuyTransaction
    {
        $txnHash = $this->createTxnHash($webElement);
        $txnHashId = TxnHashId::fromString($txnHash);

        $fromAddress = $this->createAddressFrom($webElement);
        $fromAddress = Address::fromString($fromAddress);

        $tokenId = $this->createToken($webElement);
        $tokenId = Id::fromString($tokenId);

        $tokenName = $this->createTokenName($webElement);
        $tokenName = Name::fromString($tokenName);

        $price = $this->createPriceFrom($webElement);
        $price = Price::fromFloat((float)$price);

        $transaction = BuyTransaction::writeNewFrom($tokenId, $tokenName, $txnHashId, $fromAddress, $price);
        $this->inMemoryRepository->add($transaction->txnHashId()->asString(), $transaction);
        return $transaction;
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

    public function createAddressFrom(RemoteWebElement $webElement): string
    {
        return $webElement
            ->findElement(WebDriverBy::cssSelector(RouterSelectors::FROM_ATTR))
            ->getText();
    }

    private function createPriceFrom(RemoteWebElement $webElement): string
    {
        $price = $webElement
            ->findElement(WebDriverBy::cssSelector(RouterSelectors::PRICE))
            ->getText();

        return str_replace(',', '', $price);
    }

    public function createToken(RemoteWebElement $webElement): string
    {
        $information = $webElement
            ->findElement(WebDriverBy::cssSelector(RouterSelectors::TOKEN_ATTR))
            ->getAttribute('href');

        return str_replace('/token/', '', $information);
    }

    private function createTokenAddress(RemoteWebElement $webElement): string
    {
        $information = $webElement
            ->findElement(WebDriverBy::cssSelector(RouterSelectors::TOKEN_ATTR))
            ->getText();

        $str = strstr($information, "(");
        $chain = str_replace(['(', ')'], '', $str);

        return strtolower($chain);
    }

    private function createTxnHash(RemoteWebElement $webElement): ?string
    {
        return $webElement
            ->findElement(WebDriverBy::cssSelector(RouterSelectors::HASH_TXN))
            ->getAttribute('href');
    }

    public function createTokenName(RemoteWebElement $webElement): string
    {
        $information = $webElement
            ->findElement(WebDriverBy::cssSelector(RouterSelectors::TOKEN_TEXT))
            ->getText();

        $str = strstr($information, "(");
        $chain = str_replace(['(', ')', '...'], '', $str);
        $chain = str_replace(strtolower("BSC-US..."), "bsc-usd", $chain);


        return strtolower($chain);
    }

    private function createTokenNameForBuyTransaction(RemoteWebElement $webElement): string
    {
        $information = $webElement
            ->findElement(WebDriverBy::cssSelector(RouterSelectors::TOKEN_ATTR))
            ->getText();

        $str = strstr($information, "(");
        $chain = str_replace(['(', ')'], '', $str);

        return strtolower($chain);
    }

    public function createTypeFrom(RemoteWebElement $webElement): string
    {
        $transactionType = $webElement
            ->findElement(WebDriverBy::cssSelector(RouterSelectors::FROM_TEXT))
            ->getText();

        return trim($transactionType);
    }

}