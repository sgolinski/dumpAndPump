<?php

namespace App\Domain\Event;

use App\Domain\ValueObjects\Address;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\TxnHashId;
use App\Infrastructure\DomainEvent;
use DateTimeImmutable;

class BuyTransactionWasCached implements DomainEvent
{
    private Name $name;
    private TxnHashId $txnHashId;
    private Price $price;
    private Address $fromAddress;

    public function __construct(
        TxnHashId $txnHashId,
        Address   $fromAddress,
        Price     $price,
        Name      $name
    )
    {
        $this->txnHashId = $txnHashId;
        $this->fromAddress = $fromAddress;
        $this->price = $price;
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

    public function fromAddress(): Address
    {
        return $this->fromAddress;
    }

    public function price(): Price
    {
        return $this->price;
    }

    public function txnHashId(): TxnHashId
    {
        return $this->txnHashId;
    }

}