<?php

namespace App\Domain;

use App\Application\Validation\Allowed;
use App\Domain\Event\PotentialDumpAndPumpRecognized;
use App\Domain\Event\SaleTransactionWasCached;
use App\Domain\Event\SaleTransactionWasRegistered;
use App\Domain\Event\TransactionWasRepeated;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Holders;
use App\Domain\ValueObjects\Id;
use App\Domain\ValueObjects\Price;
use App\Infrastructure\AggregateRoot;

/*TODO class need to be refactored*/

class Transaction extends AggregateRoot
{
    public Id $id;
    public Name $name;
    public Price $price;
    public Name $exchangeName;
    private Holders $holders;
    private int $repetitions;
    private array $prices;


    public function __construct(Id $id)
    {
        $this->id = $id;
        $this->repetitions = 1;
        $this->completed = false;
        $this->blacklisted = false;
        $this->isDumpAndPump = false;
        $this->isRegistered = false;
        $this->isSent = false;
        $this->isListed = false;
        $this->prices = [];
    }

    public static function writeNewFrom(
        Id    $id,
        Price $price,
        Name  $chain
    ): self
    {
        $transaction = new self($id);

        $transaction->recordAndApply(new SaleTransactionWasCached(
            $id,
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

    public function noticeRepetitions(Price $price, Name $chain): void
    {
        $this->recordAndApply(new TransactionWasRepeated($price, $chain));
    }

    public static function reconstitute(string $id, string $events): self
    {
        $transaction = new self(Id::fromString($id));
        foreach (unserialize($events) as $event) {
            $transaction->recordAndApply($event);
        }

        return $transaction;
    }

    public function applyTransactionWasCached(SaleTransactionWasCached $event): void
    {
        $this->exchangeName = $event->address();
        $this->price = $event->price();
        $this->prices[] = $this->price->asFloat();
    }

    public function applyTransactionWasRepeated(TransactionWasRepeated $event): void
    {
        if (in_array($event->price()->asFloat(), $this->prices)) {
            return;
        }
        $this->price = Price::fromFloat($this->price->asFloat() + $event->price()->asFloat());
        $this->repetitions++;
    }

    public function recognizePumpAndDump(): void
    {
        $this->recordAndApply(new PotentialDumpAndPumpRecognized());
    }

    public function applyPotentialDumpAndPumpRecognized(): void
    {
        $this->isDumpAndPump = true;
    }

    public function registerTransaction(): void
    {
        $this->recordAndApply(new SaleTransactionWasRegistered(
                $this->id,
                $this->name,
                $this->exchangeName,
                $this->price
            )
        );
    }

    public function applyTransactionWasRegistered(SaleTransactionWasRegistered $event): void
    {
        $this->name = $event->name();
        $this->price = $event->price();
        $this->exchangeName = $event->address();
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

    public function putOnBlacklist(string $holders): void
    {
        $this->recordAndApply(new TransactionBlacklisted($this->id, $holders));
    }

    public function applyTransactionBlacklisted(TransactionBlacklisted $event): void
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
        return $this->price->asFloat() === $price->asFloat();
    }

    public function ensurePriceIsHighEnough(): bool
    {
        if ($this->price->asFloat() < Allowed::PRICE_PER_NAME[$this->exchangeName->asString()]) {
            return false;
        }
        return true;
    }

    public function sendNotification(): void
    {
        $this->recordAndApply(new TransactionWasSent($this->id()));
    }

    public function applyTransactionWasSent(TransactionWasSent $event): void
    {
        $this->isSent = true;
    }

    public function createMessage(): string
    {
        return "Name: " . $this->name->asString() . PHP_EOL .
            "Drop Value: -" . $this->price->asFloat() . ' ' . $this->exchangeName->asString() . PHP_EOL .
            "Listing: https://www.coingecko.com/en/coins/" . $this->id()->asString() . PHP_EOL .
            "Poocoin:  https://poocoin.app/tokens/" . $this->id->asString() . PHP_EOL .
            'Token Sniffer: https://tokensniffer.com/token/' . $this->id()->asString() . PHP_EOL .
            'Chain: ' . $this->exchangeName->asString() . PHP_EOL;
    }

    public function showRepetitions(): int
    {
        return $this->repetitions;
    }

    public function putTransactionOnListed(Transaction $notCompletedTransaction)
    {
        $this->recordAndApply(new TransactionIsListed());
    }

    public function putTransactionOnNotListed(Transaction $notCompletedTransaction)
    {
        $this->recordAndApply(new TransactionIsNotListed());
    }

    public function applyTransactionIsListed(TransactionIsListed $event)
    {
        $this->isListed = true;
    }

    public function applyTransactionIsNotListed(TransactionIsNotListed $event)
    {
        $this->isListed = false;
    }
}