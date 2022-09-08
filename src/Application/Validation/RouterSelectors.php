<?php

namespace App\Application\Validation;

class RouterSelectors
{

    // #tblResult > tbody > tr:nth-child(1) > td:nth-child
    public const FOR_CONTENT_TABLE = '#tblResult > tbody';

    public const FOR_TYPE = 'tr > td:nth-child(5)';
    public const FOR_TABLE = '#content > div.container.space-bottom-2 > div > div.card-body';
    #tblResult > tbody > tr:nth-child(1) > td:nth-child(9) > a
    public const FOR_NAME = 'tr > td:nth-child(5) > a';
    public const FOR_PRICE = 'tr > td:nth-child(8)';
    public const FOR_HOLDERS = '#ContentPlaceHolder1_tr_tokenHolders > div > div.col-md-8 > div > div';
    #tblResult > tbody > tr:nth-child(1) > td:nth-child(9)
    const FOR_CHAIN = 'tr > td:nth-child(9) > a';
    const  FOR_KIND_TRANSACTION = 'tr > td:nth-child(5) > i';
    const HASH_TXN = 'tr > td:nth-child(2) > span > a';
    const  FOR_TO = 'tr > td:nth-child(7)';
    #ContentPlaceHolder1_maintable > div:nth-child(12) > div.col-md-9
    const FOR_TXN = ' #ContentPlaceHolder1_maintable > div:nth-child(12) > div.col-md-9 > #wrapperContent';

    #wrapperContent > li:nth-child(1) > div > a
    const FOR_SOLD_TOKEN_CON_START = 'li:nth-child(';
    const FOR_SOLD_TOKEN_CON_END = ') > div > a';
}
