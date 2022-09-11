<?php

namespace App\Domain;

use App\Domain\ValueObjects\Address;
use App\Domain\ValueObjects\Id;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\TxnHashId;
use App\Domain\ValueObjects\Type;

class BuyTransaction implements TransactionInterface
{
    private TxnHashId $txnHashId;
    private Address $fromAddress;
    private Id $id;
    private Name $name;
    private Price $price;
    private Type $type;


    public function __construct(
        Id        $id,
        Name      $name,
        TxnHashId $txnHashId,
        Address   $fromAddress,
        Price     $price,
        Type      $type,
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->txnHashId = $txnHashId;
        $this->fromAddress = $fromAddress;
        $this->price = $price;
        $this->type = $type;
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

    public function fromAddress(): Address
    {
        return $this->fromAddress;
    }
}