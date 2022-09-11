<?php

namespace App\Infrastructure;

use App\Application\Validation\Blacklisted;
use App\Application\Validation\LiquiditySelectors;
use App\Application\Validation\Selectors;
use App\Domain\Transaction;
use App\Domain\ValueObjects\ExchangeChain;
use App\Domain\ValueObjects\Id;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Infrastructure\Repository\RedisRepository;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use InvalidArgumentException;

class LiquidityTransactionFactory
{
    private RedisRepository $repository;

    public function __construct()
    {
        $this->repository = new RedisRepository();
    }

    public function createTransaction(RemoteWebElement $webElement): string
    {

        $hash = $this->createHashTransaction($webElement);
        var_dump($hash);
        $address = $this->createAddressFrom($webElement);
        var_dump($address);
        $router = $this->createAddressRouterFrom($webElement);
        var_dump($router);

        die;
//        $events = $this->repository->byId($id->asString());
//        if ($events) {
//            return Transaction::reconstitute($id->asString(), $events);
//        }
//
////            $type = $this->createTypeTransaction($webElement);
////            $hash = $this->createHashTransaction($webElement);
//        $this->createSecondAddress($webElement);
////            var_dump($hash);
//        var_dump($id->asString());
////            var_dump($type);
////            var_dump($secondEntry);
//        echo PHP_EOL;
//        return Transaction::writeNewFrom(
//            $id,
//            $this->createPriceFrom($webElement),
//            $this->createExchangeChain($webElement)
//        );
        return $hash;
    }

    public function createAddressFrom(RemoteWebElement $webElement): string
    {
        $transactionType = $webElement
            ->findElement(WebDriverBy::cssSelector(LiquiditySelectors::FOR_NAME))->getText();


//        if (in_array($id->asString(), Blacklisted::ADDRESSES, true)) {
//            throw new InvalidArgumentException('Blacklisted!');
//        }
        return $transactionType;
    }

    public function createTransactionHAsh(RemoteWebElement $webElement): string
    {
        $hash = $webElement
            ->findElement(WebDriverBy::cssSelector(LiquiditySelectors::HASH_TXN))->getText();


//        if (in_array($id->asString(), Blacklisted::ADDRESSES, true)) {
//            throw new InvalidArgumentException('Blacklisted!');
//        }
        return $hash;
    }

    private function createAddressRouterFrom(RemoteWebElement $webElement): string
    {
        $router = $webElement
            ->findElement(WebDriverBy::cssSelector(LiquiditySelectors::FOR_ROUTER_ADDRESS))
            ->getAttribute('data-original-title');



        return $router;
    }

    private function createPriceFrom(RemoteWebElement $webElement): Price
    {
        $information = $webElement
            ->findElement(WebDriverBy::cssSelector(Selectors::FOR_PRICE))
            ->getText();


        return Price::fromFloat((float)$information);
    }

    private function createExchangeChain(RemoteWebElement $webElement): ExchangeChain
    {
        $information = $webElement
            ->findElement(WebDriverBy::cssSelector(Selectors::FOR_CHAIN))
            ->getText();

        $str = strstr($information, "(");
        $chain = str_replace(['(', ')'], '', $str);
        $chain = str_replace("BSC-US...", "bsc-usd", $chain);


        return ExchangeChain::fromString(strtolower($chain));
    }

    private function createTypeTransaction(RemoteWebElement $webElement): ?string
    {
        $type = $webElement
            ->findElement(WebDriverBy::cssSelector(Selectors::FOR_KIND_TRANSACTION))
            ->getAttribute('data-original-title');


        return $type;
    }

    private function createHashTransaction(RemoteWebElement $webElement): ?string
    {
        $type = $webElement
            ->findElement(WebDriverBy::cssSelector(Selectors::HASH_TXN))
            ->getAttribute('href');


        return $type;
    }

    private function createSecondAddress(RemoteWebElement $webElement)
    {
        $elements = $webElement
            ->findElement(WebDriverBy::cssSelector(Selectors::FOR_TO));


        var_dump($elements->getTagName());
        return $elements;
    }
}