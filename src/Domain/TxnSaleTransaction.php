<?php

namespace App\Domain;

use App\Domain\Event\SaleTransactionWasCached;

use App\Domain\ValueObjects\Address;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\TxnHashId;
use App\Domain\ValueObjects\Type;
use App\Infrastructure\AggregateRoot;

class TxnSaleTransaction extends AggregateRoot implements TransactionInterface
{
    private TxnHashId $txnHashId;
    private bool $highPrice;
    private Name $name;
    private Address $address;
    private Price $price;
    private Type $type;

    public function __construct(
        TxnHashId $id,
    )
    {
        $this->txnHashId = $id;
    }

    public static function writeNewFrom(
        TxnHashId $id,
        Name      $name,
        Address   $address,
        Price     $price,
        bool      $highPrice,
        Type      $type
    ): self
    {
        $transaction = new self($id);

        $transaction->recordAndApply(new SaleTransactionWasCached(
            $name,
            $address,
            $price,
            $highPrice,
            $type
        ));
        return $transaction;
    }

    public function applySaleTransactionWasCached(SaleTransactionWasCached $event): void
    {
        $this->name = $event->chainName();
        $this->address = $event->address();
        $this->price = $event->price();
        $this->highPrice = $event->highPrice();

    }

    public function id(): TxnHashId
    {
        return $this->txnHashId;
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