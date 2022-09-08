<?php

namespace App\Application\Validation;

class Allowed
{
    public const NAMES = [
        'wbnb', 'cake', 'bnb', 'usdc', 'busd', 'usdt', 'fusdt', 'usdp', 'bsc-usd', 'eth', 'cake', 'btcb'
    ];

    public const PRICE_PER_NAME =
        [
            'wbnb' => 5.00,
            'cake' => 520.00,
            'bnb' => 5.00,
            'usdc' => 1200.00,
            'busd' => 1200.00,
            'usdt' => 1200.00,
            'fusdt' => 1200.00,
            'usdp' => 1200.00,
            'bsc-usd' => 1200.00,
            'bscusd' => 1200.00,
            'btcb' => 0.1,
            'eth' => 1.0
        ];

    public const STATUSES = [
        'complete',
        'notComplete',
        'blacklisted',
        'sent',
        'listed',
        'notListed'
    ];

    public const ADDRESSES = [
        '0xbb4cdb9cbd36b01bd1cbaebf2de08d9173bc095c',
        '0xe9e7CEA3DedcA5984780Bafc599bD69ADd087D56',
        '0x55d398326f99059fF775485246999027B3197955',
        '0x0E09FaBB73Bd3Ade0a17ECC321fD13a19e81cE82'];
}