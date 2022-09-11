<?php

namespace App\Domain\Event;

use App\Domain\ValueObjects\Address;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\TxnHashId;
use App\Infrastructure\DomainEvent;
use DateTimeImmutable;

class SaleTransactionWasRegistered implements DomainEvent
{
    private TxnHashId $txnHashId;
    private Name $name;
    private Address $address;
    private Price $price;

    public function __construct(
        TxnHashId $id,
        Name      $name,
        Address   $address,
        Price     $price,
    )
    {
        $this->txnHashId = $id;
        $this->name = $name;
        $this->address = $address;
        $this->price = $price;
    }

    public function txnHashId(): TxnHashId
    {
        return $this->txnHashId;
    }

    public function name(): Name
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

    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

}