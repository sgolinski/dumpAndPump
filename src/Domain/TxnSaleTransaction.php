<?php

namespace App\Domain;

use App\Domain\ValueObjects\Address;
use App\Domain\ValueObjects\Id;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\TxnHashId;
use App\Domain\ValueObjects\Type;


class TxnSaleTransaction implements TransactionInterface
{
    private Id $id;
    private bool $highPrice;
    private Name $name;
    private Address $address;
    private Price $price;
    private Type $type;
    private TxnHashId $txnHashId;

    public function __construct(
        TxnHashId $id,
        Name      $name,
        Address   $address,
        Price     $price,
        bool      $highPrice,
        Type      $type
    )
    {
        $this->id = Id::fromString($id->asString());
        $this->name = $name;
        $this->address = $address;
        $this->price = $price;
        $this->highPrice = $highPrice;
        $this->type = $type;
        $this->txnHashId = $id;
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function txnHashId(): TxnHashId
    {
        return $this->txnHashId;
    }

    public function price(): Price
    {
        return $this->price;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function name(): Name
    {
        return $this->name;
    }
}