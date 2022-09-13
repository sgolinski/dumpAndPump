<?php

namespace App\Application;

class FindBiggestSaleTransaction
{
    private string $notComplete = 'notComplete';
    private string $liquidity = 'lp';

    public function notComplete(): string
    {
        return $this->notComplete;
    }

    public function liquidity(): string
    {
        return $this->liquidity;
    }
}