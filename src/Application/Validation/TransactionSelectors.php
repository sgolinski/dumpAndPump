<?php

namespace App\Application\Validation;

class TransactionSelectors
{

    public const FOR_NAME = 'tr > td:nth-child(5) > a';

    public const FOR_PRICE = 'tr > td:nth-child(8)';

    public const FOR_HOLDERS = '#ContentPlaceHolder1_tr_tokenHolders > div > div.col-md-8 > div > div';

    const FOR_CHAIN = 'tr > td:nth-child(9)';

    const  FOR_KIND_TRANSACTION = 'tr > td:nth-child(5) > i';

    const HASH_TXN = 'tr > td:nth-child(2) > span > a';

    const  FOR_TO = 'tr > td:nth-child(7)';

}
