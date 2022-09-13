<?php

namespace App\Application\Validation;

class RouterSelectors
{

    /**
     * Txn Hash used as ID in TxnSaleTranasction
     */
    const HASH_TXN = 'tr > td:nth-child(2) > span > a';

    /**
     *  FROM is used to create address in BuyTransaction
     */
    public const FROM_ATTR = 'tr > td:nth-child(5) > a';
    public const FROM_TEXT = 'tr > td:nth-child(5)';
    public const FROM_DATA_TYPE = 'tr > td:nth-child(5) > i';
    /**
     * VALUE is used to create price
     */
    public const PRICE = 'tr > td:nth-child(8)';

#tblResult > tbody > tr:nth-child(3) > td:nth-child(5) > i
    /**
     * TOKEN us use to create Chain
     */
    const TOKEN_ATTR = 'tr > td:nth-child(9) > a';
    const TOKEN_TEXT = 'tr > td:nth-child(9)';

#tblResult > tbody > tr:nth-child(3) > td:nth-child(9) > a

    public const FOR_CONTENT_TABLE = '#tblResult > tbody';


    public const FOR_TABLE = '#content > div.container.space-bottom-2 > div > div.card-body';
    #tblResult > tbody > tr:nth-child(1) > td:nth-child(9) > a


    public const FOR_HOLDERS = '#ContentPlaceHolder1_tr_tokenHolders > div > div.col-md-8 > div > div';
    #tblResult > tbody > tr:nth-child(1) > td:nth-child(9)


    #tblResult > tbody > tr:nth-child(8) > td:nth-child(7) > span > a

    const  FOR_TO = 'tr > td:nth-child(7)';
    #ContentPlaceHolder1_maintable > div:nth-child(12) > div.col-md-9
    const FOR_TXN = ' #ContentPlaceHolder1_maintable > div:nth-child(12) > div.col-md-9 > #wrapperContent';

    #wrapperContent > li:nth-child(1) > div > a
    const FOR_SOLD_TOKEN_CON_START = 'li:nth-child(';
    const FOR_SOLD_TOKEN_CON_END = ') > div > a';
}
