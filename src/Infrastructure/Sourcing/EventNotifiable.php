<?php

namespace Domain\Event\Sourcing;

interface EventNotifiable
{
    public function notifyDispatchableEvents();
}
