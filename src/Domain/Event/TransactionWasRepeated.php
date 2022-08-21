<?php

namespace App\Domain\Event;

use App\Domain\ValueObjects\ExchangeChain;
use App\Domain\ValueObjects\Price;
use App\Infrastructure\DomainEvent;
use DateTimeImmutable;

class TransactionWasRepeated implements DomainEvent
{
    private Price $price;
    private ExchangeChain $exchangeChain;

    public function __construct(Price $price, ExchangeChain $chain)
    {
        $this->price = $price;
        $this->exchangeChain = $chain;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    /**
     * @return ExchangeChain
     */
    public function exchangeChain(): ExchangeChain
    {
        return $this->exchangeChain;
    }

    /**
     * @return Price
     */
    public function price(): Price
    {
        return $this->price;
    }
}