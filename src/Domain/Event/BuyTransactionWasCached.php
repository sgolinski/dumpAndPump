<?php

namespace App\Domain\Event;

use App\Domain\ValueObjects\Address;
use App\Domain\ValueObjects\ExchangeChain;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Infrastructure\DomainEvent;
use DateTimeImmutable;

class BuyTransactionWasCached implements DomainEvent
{
    private Name $name;
    private Address $txnAddress;
    private Price $price;
    private ExchangeChain $chain;

    public function __construct(
        Address       $txnAddress,
        Price         $price,
        ExchangeChain $chain,
        Name          $name
    )
    {
        $this->txnAddress = $txnAddress;
        $this->price = $price;
        $this->chain = $chain;
        $this->name = $name;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function chain(): ExchangeChain
    {
        return $this->chain;
    }

    public function price(): Price
    {
        return $this->price;
    }

    public function txnAddress(): Address
    {
        return $this->txnAddress;
    }

}