<?php

namespace App\Domain\Event;

use App\Domain\ValueObjects\ExchangeChain;
use App\Domain\ValueObjects\Id;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Infrastructure\DomainEvent;
use DateTimeImmutable;

class TransactionWasRepeated implements DomainEvent
{

    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}