<?php

namespace App\Domain\Event;

use App\Infrastructure\DomainEvent;
use DateTimeImmutable;

class PotentialDumpAndPumpRecognized implements DomainEvent
{
    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
