<?php

namespace App\Domain;

use App\Domain\Event\BuyTransactionWasCached;
use App\Domain\Event\PotentialDumpAndPumpRecognized;
use App\Domain\Event\SaleTransactionWasCached;
use App\Domain\ValueObjects\Address;
use App\Domain\ValueObjects\ExchangeChain;
use App\Domain\ValueObjects\Id;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Infrastructure\AggregateRoot;

/*TODO class need to be refactored*/

class BuyTransaction extends AggregateRoot implements TransactionInterface
{
    private Name $chainName;
    private Address $txnAddress;
    private ExchangeChain $chain;
    private Price $price;
    private Id $id;

    public function __construct(
        Id $id,
    )
    {
        $this->id = $id;
    }

    public static function writeNewFrom(
        Id            $id,
        Name          $name,
        Address       $txnAddress,
        ExchangeChain $chain,
        Price         $price,
    ): self
    {
        $transaction = new self($id);

        $transaction->recordAndApply(new BuyTransactionWasCached(
            $txnAddress,
            $price,
            $chain,
            $name,
        ));
        return $transaction;
    }

    public function applyBuyTransactionWasCached(BuyTransactionWasCached $event): void
    {
        $this->chainName = $event->name();
        $this->txnAddress= $event->txnAddress();
        $this->chain = $event->chain();
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
}