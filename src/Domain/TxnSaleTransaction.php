<?php

namespace App\Domain;


use App\Domain\Event\SaleTransactionWasCached;
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

    public function applyTransactionWasCached(SaleTransactionWasCached $event): void
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
}