<?php

namespace App\Domain\Event;

use App\Infrastructure\DomainEvent;
use DateTimeImmutable;

class TransactionWasRegistered implements DomainEvent
{
    public function __construct()
    {
    }

    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}