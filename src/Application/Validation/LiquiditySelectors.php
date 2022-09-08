<?php

namespace App\Application\Validation;

class LiquiditySelectors
{

    public const FOR_NAME = 'tr > td:nth-child(5) > a';

    public const FOR_PRICE = 'tr > td:nth-child(8)';

    public const FOR_HOLDERS = '#ContentPlaceHolder1_tr_tokenHolders > div > div.col-md-8 > div > div';

    const FOR_CHAIN = 'tr > td:nth-child(9)';

    const  FOR_KIND_TRANSACTION = 'tr > td:nth-child(5) > i';

    // #tblResult > tbody > tr:nth-child(1) > td:nth-child(2) > span
    const HASH_TXN = 'tr > td:nth-child(2) > span';

    const  FOR_TO = 'tr > td:nth-child(7)';
// attribute data-original-title
    const FOR_ROUTER_ADDRESS = 'tr > td:nth-child(7) > span > a';

}
