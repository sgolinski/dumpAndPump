<?php

namespace App\Application\Validation;

class Allowed
{
    public const NAMES = [
        'wbnb', 'cake', 'bnb', 'usdc', 'busd', 'usdt', 'fusdt', 'usdp', 'bsc-usd', 'eth', 'cake', 'btcb'
    ];

    public const PRICE_PER_NAME =
        [
            'wbnb' => 16.00,
            'cake' => 1520.00,
            'bnb' => 16.00,
            'usdc' => 4900.00,
            'busd' => 4900.00,
            'usdt' => 4900.00,
            'fusdt' => 4900.00,
            'usdp' => 4900.00,
            'bsc-usd' => 4900.00,
            'bscusd' => 4900.00,
            'btcb' => 0.3,
            'eth' => 3.0
        ];

    public const STATUSES = [
        'complete',
        'notComplete',
        'blacklisted',
        'sent',
        'listed',
        'notListed'
    ];
}