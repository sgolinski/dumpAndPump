<?php

namespace App\Application;

class FillNotCompleteTransaction
{
    private string $notComplete = 'notComplete';
    private string $blacklist = 'blacklisted';
    private string $complete = 'complete';

    public function notComplete(): string
    {
        return $this->notComplete;
    }

    public function blacklist(): string
    {
        return $this->blacklist;
    }

    public function complete(): string
    {
        return $this->complete;
    }
}