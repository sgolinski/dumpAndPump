<?php

namespace App\Domain\Event;

use App\Domain\ValueObjects\Id;
use App\Infrastructure\DomainEvent;
use DateTimeImmutable;

class TransactionWasSent implements DomainEvent
{
    private Id $id;

    public function __construct(Id $id)
    {
        $this->id = $id;
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}