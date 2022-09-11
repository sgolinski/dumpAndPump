<?php

namespace App\Application\Validation;

class Allowed
{
    public const NAMES = [
        'wbnb', 'cake', 'bnb', 'usdc', 'busd', 'usdt', 'fusdt', 'usdp', 'bsc-usd', 'eth', 'cake', 'btcb', 'cake-l'
    ];

    public const PRICE_PER_NAME =
        [
            'wbnb' => 5.00,
            'cake' => 520.00,
            'cake-l' => 10.00,
            'bnb' => 5.00,
            'usdc' => 1200.00,
            'busd' => 1200.00,
            'usdt' => 1200.00,
            'fusdt' => 1200.00,
            'usdp' => 1200.00,
            'bsc-usd' => 1200.00,
            'bsc-us' => 1200.00,
            'bscusd' => 1200.00,
            'btcb' => 0.1,
            'eth' => 1.0
        ];
    public const MIN_PRICE_PER_NAME =
        [
            'wbnb' => 0.01,
            'cake' => 6.00,
            'cake-l' => 1.00,
            'bnb' => 0.01,
            'usdc' => 3.00,
            'busd' => 3.00,
            'usdt' => 3.00,
            'fusdt' => 3.00,
            'usdp' => 3.00,
            'bsc-usd' => 3.00,
            'bsc-us' => 3.00,
            'bscusd' => 3.00,
            'btcb' => 0.00015,
            'eth' => 0.002
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

    public const EXCHANGE_CHAINS = [
        'busd', 'bsc-usd', 'wbnb', 'cake-l', 'usdc', 'bnb', 'ustc', 'cake', 'bsc-us'
    ];

    public const ROUTER_NAMES = [
        'Pancake LPs', 'PancakeSwap V2', ' PancakeSwap:'
    ];


}
