<?php

namespace App\Domain;

use App\Domain\Event\BuyTransactionWasCached;
use App\Domain\Event\PotentialDumpAndPumpRecognized;
use App\Domain\ValueObjects\Address;
use App\Domain\ValueObjects\Id;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\TxnHashId;
use App\Infrastructure\AggregateRoot;

class BuyTransaction extends AggregateRoot implements TransactionInterface
{
    private TxnHashId $txnHashId;
    private Address $fromAddress;
    private Id $id;
    private Name $name;
    private Price $price;


    public function __construct(
        Id $id,
    )
    {
        $this->id = $id;
    }

    public static function writeNewFrom(
        Id        $id,
        Name      $name,
        TxnHashId $txnHashId,
        Address   $fromAddress,
        Price     $price,
    ): self
    {
        $transaction = new self($id);

        $transaction->recordAndApply(new BuyTransactionWasCached(
            $txnHashId,
            $fromAddress,
            $price,
            $name,
        ));
        return $transaction;
    }

    public function applyBuyTransactionWasCached(BuyTransactionWasCached $event): void
    {
        $this->name = $event->name();
        $this->txnHashId = $event->txnHashId();
        $this->fromAddress = $event->fromAddress();
        $this->price = $event->price();
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function recognizePumpAndDump(): void
    {
        $this->recordAndApply(new PotentialDumpAndPumpRecognized());
    }

    /**
     * @return TxnHashId
     */
    public function txnHashId(): TxnHashId
    {
        return $this->txnHashId;
    }
}