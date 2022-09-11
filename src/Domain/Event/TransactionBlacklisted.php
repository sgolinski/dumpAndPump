<?php

namespace App\Domain\Event;

use App\Domain\ValueObjects\Holders;
use App\Domain\ValueObjects\Id;
use App\Infrastructure\DomainEvent;
use DateTimeImmutable;

class TransactionBlacklisted implements DomainEvent
{
    private Id $id;
    private Holders $holders;

    public function __construct(
        Id     $id,
        string $holders
    )
    {
        $this->id = $id;
        $this->holders = Holders::fromString($holders);
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function holders(): Holders
    {
        return $this->holders;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}