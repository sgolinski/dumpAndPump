<?php

namespace App\Domain\Event;

use App\Domain\ValueObjects\ExchangeChain;
use App\Domain\ValueObjects\Id;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\TxnHashId;
use App\Infrastructure\DomainEvent;
use DateTimeImmutable;

class TransactionWasCached implements DomainEvent
{
    private Id $id;
    private Name $name;
    private Price $price;
    private Name $exchangeName;
    private TxnHashId $txnHashId;

    public function __construct(
        Id        $id,
        Name      $name,
        Price     $price,
        Name      $exchangeName,
        TxnHashId $txnHashId,
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->exchangeName = $exchangeName;
        $this->txnHashId = $txnHashId;
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function price(): Price
    {
        return $this->price;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function txnHashId(): TxnHashId
    {
        return $this->txnHashId;
    }

    /**
     * @return Name
     */
    public function exchangeName(): Name
    {
        return $this->exchangeName;
    }

}