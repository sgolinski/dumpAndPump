<?php

namespace App\Application;

class FindBiggestSaleTransaction
{
    private string $status = 'notComplete';

    public function notComplete(): string
    {
        return $this->status;
    }
}