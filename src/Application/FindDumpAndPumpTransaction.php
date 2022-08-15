<?php

namespace App\Application;

class FindDumpAndPumpTransaction
{
    private string $status = 'notComplete';

    public function notComplete(): string
    {
        return $this->status;
    }
}