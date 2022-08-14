<?php

namespace Domain\Event\Sourcing;

use Domain\Event\DomainEvent;

class DispatchableDomainEvent
{
    private DomainEvent $domainEvent;

    private int $eventId;

    public function __construct($anEventId, DomainEvent $aDomainEvent)
    {
        $this->domainEvent = $aDomainEvent;
        $this->eventId = $anEventId;
    }

    public function domainEvent(): DomainEvent
    {
        return $this->domainEvent;
    }

    public function eventId(): int
    {
        return $this->eventId;
    }
}
