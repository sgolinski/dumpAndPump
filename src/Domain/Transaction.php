<?php

namespace App\Domain;

use App\Application\Validation\Allowed;
use App\Domain\Event\HoldersWereAssigned;
use App\Domain\Event\PotentialDumpAndPumpRecognized;
use App\Domain\Event\TransactionBlacklisted;
use App\Domain\Event\TransactionCompleted;
use App\Domain\Event\TransactionWasCached;
use App\Domain\Event\TransactionWasRegistered;
use App\Domain\Event\TransactionWasRepeated;
use App\Domain\ValueObjects\ExchangeChain;
use App\Domain\ValueObjects\Holders;
use App\Domain\ValueObjects\Id;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\Url;
use App\Infrastructure\AggregateRoot;

class Transaction extends AggregateRoot
{
    public Id $id;
    public Name $name;
    public Price $price;
    public ExchangeChain $exchangeChain;
    private Holders $holders;
    private Url $url;
    private int $repetitions;
    private bool $completed = false;
    private bool $blacklisted = false;
    private bool $isSent = false;
    private bool $isDumpAndPump = false;
    private bool $isRegistered = false;


    public function __construct(Id $id)
    {
        $this->id = $id;
        $this->repetitions = 1;
    }

    public static function writeNewFrom(
        Id            $id,
        Name          $name,
        Price         $price,
        ExchangeChain $chain
    ): self
    {
        $transaction = new self($id);

        $transaction->recordAndApply(new TransactionWasCached(
            $id,
            $name,
            $chain,
            $price
        ));
        return $transaction;
    }

    public function id(): id
    {
        return $this->id;
    }

    public function price(): Price
    {
        return $this->price;
    }

    public function noticeRepetitions(): void
    {
        $this->recordAndApply(new TransactionWasRepeated());
    }

    public static function reconstitute(string $id, string $events): self
    {
        $transaction = new self(Id::fromString($id));
        foreach (unserialize($events) as $event) {
            $transaction->applyThat($event);
        }
        return $transaction;
    }

    public function applyTransactionWasCached(TransactionWasCached $event): void
    {
        $this->name = $event->name();
        $this->exchangeChain = $event->chain();
        $this->price = $event->price();
    }

    public function applyTransactionWasRepeated(): void
    {
        $this->repetitions++;
    }

    public function pumpAndDumpRecognized(): void
    {
        $this->recordAndApply(new PotentialDumpAndPumpRecognized());
    }

    public function applyPotentialDumpAndPumpRecognized(): void
    {
        $this->isDumpAndPump = true;
    }

    public function registerTransaction(): void
    {
        $this->recordAndApply(new TransactionWasRegistered());
    }

    public function applyTransactionWasRegistered(TransactionWasRegistered $event): void
    {
        $this->isRegistered = true;
    }

    public function assignHolders(string $holders): void
    {
        $this->recordAndApply(new HoldersWereAssigned($this->id(), $holders));
    }

    public function applyHoldersWereAssigned(HoldersWereAssigned $event): void
    {
        $this->holders = $event->holders();
    }

    public function assignToBlackList(string $holders): void
    {
        $this->recordAndApply(new TransactionBlacklisted($this->id, $holders));
    }

    public function applyTransactionBlacklisted(TransactionBlacklisted $event)
    {
        $this->blacklisted = true;
    }

    public function completeTransaction(): void
    {
        $this->recordAndApply(new TransactionCompleted($this->id));
    }

    public function applyTransactionCompleted(TransactionCompleted $event): void
    {
        $this->completed = true;
    }

    public function priceEqualTo(Price $price): bool
    {
        return $this->price == $price;
    }

    public function ensurePriceIsHighEnough(): bool
    {
        if ($this->price < Allowed::PRICE_PER_NAME[$this->exchangeChain->asString()]) {
            return false;
        }
        return true;
    }
}