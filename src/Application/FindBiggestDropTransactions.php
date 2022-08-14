<?php

namespace App\Application;

class FindBiggestDropTransactions
{
    private string $status = 'notComplete';

    public function status(): string
    {
        return $this->status;
    }
}