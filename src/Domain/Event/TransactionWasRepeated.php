<?php

namespace App\Domain\Event;

use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Infrastructure\DomainEvent;
use DateTimeImmutable;

class TransactionWasRepeated implements DomainEvent
{
    private Price $price;
    private Name $exchangeName;

    public function __construct(Price $price, Name $exchangeName)
    {
        $this->price = $price;
        $this->exchangeName = $exchangeName;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function exchangeName(): Name
    {
        return $this->exchangeName;
    }


    public function price(): Price
    {
        return $this->price;
    }
}