<?php

namespace App\Domain;


use App\Domain\Event\SaleTransactionWasCached;
use App\Domain\Event\SaleTransactionWasRegistered;
use App\Domain\Event\TransactionWasRepeated;
use App\Domain\ValueObjects\ExchangeChain;
use App\Domain\ValueObjects\Id;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Infrastructure\AggregateRoot;


class TxnSaleTransaction extends AggregateRoot implements TransactionInterface
{
    private bool $highPrice;
    private Name $chainName;
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
        Name          $chainName,
        ExchangeChain $chain,
        Price         $price,
        bool          $highPrice,
    ): self
    {
        $transaction = new self($id);

        $transaction->recordAndApply(new SaleTransactionWasCached(
            $price,
            $chain,
            $chainName,
            $highPrice
        ));
        return $transaction;
    }

    public function applySaleTransactionWasCached(SaleTransactionWasCached $event): void
    {
        $this->chainName = $event->chainName();
        $this->chain = $event->chain();
        $this->price = $event->price();
        $this->highPrice = $event->highPrice();

    }

    public function id(): Id
    {
        return $this->id;
    }

    public function registerTransaction(): void
    {
        $this->recordAndApply(new SaleTransactionWasRegistered(
                $this->id,
                $this->name,
                $this->exchangeChain,
                $this->price
            )
        );
    }

    public function applySaleTransactionWasRegistered(SaleTransactionWasRegistered $event): void
    {
        $this->name = $event->name();
        $this->price = $event->price();
        $this->exchangeChain = $event->exchangeChain();
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

}