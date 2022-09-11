<?php

namespace App\Domain;


use App\Domain\Event\SaleTransactionWasCached;
use App\Domain\Event\SaleTransactionWasRegistered;
use App\Domain\Event\TransactionWasRepeated;
use App\Domain\ValueObjects\Address;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\TxnHashId;
use App\Infrastructure\AggregateRoot;


class TxnSaleTransaction extends AggregateRoot implements TransactionInterface
{
    private TxnHashId $txnHashId;
    private bool $highPrice;
    private Name $name;
    private Address $address;
    private Price $price;

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
    ): self
    {
        $transaction = new self($id);

        $transaction->recordAndApply(new SaleTransactionWasCached(
            $name,
            $address,
            $price,
            $highPrice
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

    public function registerTransaction(): void
    {
        $this->recordAndApply(new SaleTransactionWasRegistered(
                $this->txnHashId,
                $this->name,
                $this->address,
                $this->price
            )
        );
    }

    public function applySaleTransactionWasRegistered(SaleTransactionWasRegistered $event): void
    {
        $this->name = $event->name();
        $this->price = $event->price();
        $this->address = $event->address();
        $this->isRegistered = true;
    }

    public function applyTransactionWasRepeated(TransactionWasRepeated $event): void
    {
        if (in_array($event->price()->asFloat(), $this->prices)) {
            return;
        }
        $this->price = Price::fromFloat($this->price->asFloat() + $event->price()->asFloat());
        $this->repetitions++;
    }

    public function price(): Price
    {
        return $this->price;
    }
}