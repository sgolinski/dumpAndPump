<?php

namespace App\Application;

class NotifyOnSlack
{
    private string $sent = 'sent';
    private string $complete = 'complete';

    public function complete(): string
    {
        return $this->complete;
    }

    public function sent(): string
    {
        return $this->sent;
    }
}