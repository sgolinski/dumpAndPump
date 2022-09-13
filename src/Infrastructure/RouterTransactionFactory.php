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
use App\Domain\ValueObjects\Type;
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
        Name             $tokenName,
        Price            $price,
        Type             $type,
        TxnHashId        $txnHashId
    ): TxnSaleTransaction
    {

        $tokenAddress = $this->createToken($webElement);
        $tokenAddress = Address::fromString($tokenAddress);

        $highPrice = false;
        if ($price->asFloat() >= Allowed::PRICE_PER_NAME[$tokenName->asString()]) {
            $highPrice = true;
        }

        $transaction = new TxnSaleTransaction($txnHashId, $tokenName, $tokenAddress, $price, $highPrice, $type);

        $this->inMemoryRepository->add($transaction->id()->asString(), $transaction);

        return $transaction;
    }

    public function createBuyTransaction(
        RemoteWebElement $webElement,
        Type             $type,
        TxnHashId        $txnHashId
    ): BuyTransaction
    {

        $fromAddress = $this->createAddressFrom($webElement);
        $fromAddress = Address::fromString($fromAddress);

        $tokenId = $this->createToken($webElement);
        $tokenId = Id::fromString($tokenId);

        $tokenName = $this->createTokenName($webElement);
        $price = $this->createPriceFrom($webElement);

        $transaction = new BuyTransaction($tokenId, $tokenName, $txnHashId, $fromAddress, $price, $type);
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

    public function createPriceFrom(RemoteWebElement $webElement): Price
    {
        $price = $webElement
            ->findElement(WebDriverBy::cssSelector(RouterSelectors::PRICE))
            ->getText();
        $price = str_replace(',', '', $price);
        $price = (float)$price;
        return Price::fromFloat($price);
    }

    public function createToken(RemoteWebElement $webElement): string
    {
        $information = $webElement
            ->findElement(WebDriverBy::cssSelector(RouterSelectors::TOKEN_ATTR))
            ->getAttribute('href');

        return str_replace('/token/', '', $information);
    }

    public function createType(Name $tokenName): Type
    {
        if (in_array($tokenName->asString(), Allowed::NAMES)) {
            return Type::fromString('exchange');
        }
        return Type::fromString('other');
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

    public function createTxnHash(RemoteWebElement $webElement): ?TxnHashId
    {
        $txnHash = $webElement
            ->findElement(WebDriverBy::cssSelector(RouterSelectors::HASH_TXN))
            ->getAttribute('href');

        return TxnHashId::fromString(str_replace('/tx/', '', $txnHash));
    }

    public function createTokenName(RemoteWebElement $webElement): Name
    {
        $information = $webElement
            ->findElement(WebDriverBy::cssSelector(RouterSelectors::TOKEN_TEXT))
            ->getText();

        $str = strstr($information, "(");
        $chain = str_replace(['(', ')', '...'], '', $str);
        $chain = strtolower($chain);
        $chain = str_replace(["BSC-US...", 'bsc-us'], "bsc-usd", $chain);

        return Name::fromString(strtolower($chain));
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

    public function findRouterNameFrom(RemoteWebElement $webElement): string
    {
        $transactionType = $webElement
            ->findElement(WebDriverBy::cssSelector(RouterSelectors::FROM_TEXT))
            ->getText();

        return trim($transactionType);
    }

}