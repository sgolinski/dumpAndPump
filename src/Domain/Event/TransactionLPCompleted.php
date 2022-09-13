<?php

namespace App\Domain\Event;

use App\Domain\ValueObjects\Id;
use App\Infrastructure\DomainEvent;
use DateTimeImmutable;

class TransactionLPCompleted implements DomainEvent
{

    public function __construct(Id $id)
    {
    }

    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}