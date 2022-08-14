<?php

namespace App\Application;

class FindDumpAndPumpTransaction
{
    private string $status = 'notComplete';

    public function status(): string
    {
        return $this->status;
    }
}