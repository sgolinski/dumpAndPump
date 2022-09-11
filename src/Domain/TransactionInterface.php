<?php

namespace App\Domain;

use App\Domain\ValueObjects\Price;

interface TransactionInterface
{

    public function price(): Price;
}