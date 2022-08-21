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

class TransactionFactory
{
    private RedisRepository $repository;

    public function __construct()
    {
        $this->repository = new RedisRepository();
    }

    public function createTransaction(RemoteWebElement $webElement): Transaction
    {
        try {

            $id = $this->createIdFrom($webElement);
            $events = $this->repository->byId($id->asString());
            if($events){
                return Transaction::reconstitute($id->asString(),$events);
            }

            return Transaction::writeNewFrom(
                $id,
                $this->createNameFrom($webElement),
                $this->createPriceFrom($webElement),
                $this->createExchangeChain($webElement)
            );
        } catch (Exception) {
            throw new InvalidArgumentException();
        }
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

    private function extractPriceFrom(string $float): Price
    {
        $strPrice = str_replace([','], [''], $float);
        return Price::fromFloat(round((float)$strPrice, 3));
    }

    private function extractChainFrom(string $data): ExchangeChain
    {
        return ExchangeChain::fromString(strtolower($data));
    }
}