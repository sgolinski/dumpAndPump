<?php

namespace App\Domain\Event;

use App\Domain\ValueObjects\Id;

class TransactionWasSent implements \App\Infrastructure\DomainEvent
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
}