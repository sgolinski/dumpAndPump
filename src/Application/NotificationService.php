<?php

namespace App\Application;

use Maknz\Slack\Client;
use Maknz\Slack\Message;

class NotificationService
{
    private const HOOK = 'https://hooks.slack.com/services/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';
    private Client $slack;

    public function __construct()
    {
        $this->slack = new Client(self::HOOK);
    }

    public function sendNotificationsFor(array $transactions): void
    {
        foreach ($transactions as $transaction) {
            $message = new Message();
            $message->setText($transaction->createMessage());
            $this->slack->sendMessage($message);
        }
    }

}