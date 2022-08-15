<?php

namespace App\Application;

class FindBiggestDropTransactions
{
    private string $status = 'notComplete';

    public function notComplete(): string
    {
        return $this->status;
    }
}