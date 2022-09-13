<?php

namespace App\Application;

class FillLiquidityRemovalTransaction
{
    public function lp(): string
    {
        return 'lp';
    }

    public function complete(): string
    {
        return 'complete';
    }

    public function listed(): string
    {
        return 'listed';
    }
}