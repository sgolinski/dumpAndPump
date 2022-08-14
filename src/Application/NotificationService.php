<?php

namespace App\Application;

use App\Domain\Transaction;
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
            $message->setText($this->setTemplate($transaction));
            $this->slack->sendMessage($message);
        }
    }

    private function setTemplate(Transaction $transaction): string
    {
        return "Name: " . $transaction->name->asString() . PHP_EOL .
            "Drop Value: -" . $transaction->price->asFloat() . ' ' . $transaction->exchangeChain->asString() . PHP_EOL .
            "Listing: https://www.coingecko.com/en/coins/" . $transaction->id()->asString() . PHP_EOL .
            "Poocoin:  https://poocoin.app/tokens/" . $transaction->id->asString() . PHP_EOL .
            'Token Sniffer: https://tokensniffer.com/token/' . $transaction->id()->asString() . PHP_EOL .
            'Chain: ' . $transaction->exchangeChain->asString() . PHP_EOL;
    }

}