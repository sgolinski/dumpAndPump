<?php

namespace App\Domain\Event;

use App\Domain\ValueObjects\ExchangeChain;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Infrastructure\DomainEvent;
use DateTimeImmutable;

class BuyTransactionWasCached implements DomainEvent
{
    private Price $price;
    private ExchangeChain $chain;
    private Name $chainName;


    /**
     * @param Price $price
     * @param ExchangeChain $chain
     * @param Name $chainName
     * @param bool $highPrice
     */
    public function __construct(
        Price         $price,
        ExchangeChain $chain,
        Name          $chainName)

    {
        $this->price = $price;
        $this->chain = $chain;
        $this->chainName = $chainName;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function chainName(): Name
    {
        return $this->chainName;
    }

    public function chain(): ExchangeChain
    {
        return $this->chain;
    }

    public function price(): Price
    {
        return $this->price;
    }

}