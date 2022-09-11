<?php

namespace App\Domain\Event;

use App\Domain\ValueObjects\Address;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\Type;
use App\Infrastructure\DomainEvent;
use DateTimeImmutable;

class SaleTransactionWasCached implements DomainEvent
{
    private Price $price;
    private Address $address;
    private Name $name;
    private bool $highPrice;
    private Type $type;


    public function __construct(
        Name    $name,
        Address $address,
        Price   $price,
        bool    $highPrice,
        Type    $type,
    )
    {
        $this->price = $price;
        $this->address = $address;
        $this->name = $name;
        $this->highPrice = $highPrice;
        $this->type = $type;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function chainName(): Name
    {
        return $this->name;
    }

    public function address(): Address
    {
        return $this->address;
    }

    public function price(): Price
    {
        return $this->price;
    }

    public function highPrice(): bool
    {
        return $this->highPrice;
    }

    public function type(): Type
    {
        return $this->type;
    }

}