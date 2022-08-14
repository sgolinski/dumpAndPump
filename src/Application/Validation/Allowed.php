<?php

namespace App\Application\Validation;

class Allowed
{
    public const NAMES = [
        'wbnb', 'cake', 'bnb', 'usdc', 'busd', 'usdt', 'fusdt', 'usdp', 'bsc-usd', 'eth', 'cake', 'btcb'
    ];

    public const PRICE_PER_NAME =
        [
            'wbnb' => 7.00,
            'cake' => 760.00,
            'bnb' => 7.00,
            'usdc' => 2470.00,
            'busd' => 2470.00,
            'usdt' => 2470.00,
            'fusdt' => 2470.00,
            'usdp' => 2470.00,
            'bsc-usd' => 2470.00,
            'bscusd' => 2470.00,
            'btcb' => 0.1,
            'eth' => 1.3
        ];

    public const STATUSES = [
        'complete',
        'notComplete',
        'blacklisted',
        'sent',
    ];
}