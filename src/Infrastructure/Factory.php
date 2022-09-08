<?php

namespace App\Infrastructure;

use App\Application\Validation\Blacklisted;
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

class Factory
{
    private RedisRepository $repository;

    public function __construct()
    {
        $this->repository = new RedisRepository();
    }

    public function createTransaction(RemoteWebElement $webElement): string
    {

        $type = $this->createTypeFrom($webElement);



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
        return $type;
    }

    public function createTypeFrom(RemoteWebElement $webElement): string
    {
        $transactionType = $webElement
            ->findElement(WebDriverBy::cssSelector(Selectors::FOR_TYPE))
            ->getText();


//        if (in_array($id->asString(), Blacklisted::ADDRESSES, true)) {
//            throw new InvalidArgumentException('Blacklisted!');
//        }
        return trim($transactionType);
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