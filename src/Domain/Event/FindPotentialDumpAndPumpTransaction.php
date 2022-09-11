<?php

namespace App\Domain\Event;

class FindPotentialDumpAndPumpTransaction
{
    public function notComplete(): string
    {
        return 'notComplete';
    }
}