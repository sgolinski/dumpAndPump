<?php

namespace App\Application;

class FindBiggestSaleTransaction
{
    private string $notComplete = 'notComplete';
    private string $complete = 'complete';

    public function notComplete(): string
    {
        return $this->notComplete;
    }

    public function complete(): string
    {
        return $this->complete;
    }
}