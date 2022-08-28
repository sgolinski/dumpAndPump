<?php

namespace App\Application;

class FindListedTransaction
{
    private string $listed = 'listed';
    private string $notListed = 'notListed';
    private string $notComplete = 'notComplete';

    public function notComplete(): string
    {
        return $this->notComplete;
    }

    public function listed(): string
    {
        return $this->listed;
    }

    public function notListed(): string
    {
        return $this->notListed;
    }
}