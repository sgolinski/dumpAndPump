<?php

namespace App\Application;

class FindPotentialDumpAndPumpTransaction
{
    private string $status = 'notComplete';

    public function notComplete(): string
    {
        return $this->status;
    }
}