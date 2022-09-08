<?php

namespace App\Domain;

use App\Domain\Event\BuyTransactionWasCached;
use App\Domain\Event\SaleTransactionWasCached;
use App\Domain\ValueObjects\ExchangeChain;
use App\Domain\ValueObjects\Id;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Infrastructure\AggregateRoot;

/*TODO class need to be refactored*/

class BuyTransaction extends AggregateRoot implements TransactionInterface
{
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
    ): self
    {
        $transaction = new self($id);

        $transaction->recordAndApply(new BuyTransactionWasCached(
            $price,
            $chain,
            $chainName,
        ));
        return $transaction;
    }

    public function applyTransactionWasCached(SaleTransactionWasCached $event): void
    {
        $this->chainName = $event->chainName();
        $this->chain = $event->chain();
        $this->price = $event->price();
    }

    public function id(): Id
    {
        return $this->id;
    }
}