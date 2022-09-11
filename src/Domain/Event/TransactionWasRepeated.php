<?php

namespace App\Domain\Event;

use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Infrastructure\DomainEvent;
use DateTimeImmutable;


class TransactionWasRepeated implements DomainEvent
{
    private Price $price;
    private Name $exchangeChain;

    public function __construct(Price $price, Name $chain)
    {
        $this->price = $price;
        $this->exchangeChain = $chain;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function exchangeChain(): Name
    {
        return $this->exchangeChain;
    }


    public function price(): Price
    {
        return $this->price;
    }

}