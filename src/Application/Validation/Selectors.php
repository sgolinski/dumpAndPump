<?php

namespace App\Application\Validation;

class Selectors
{
    public const FOR_TABLE = '#content > div.container.space-bottom-2 > div > div.card-body';
    public const FOR_TABLE_BODY = 'table.table-hover > tbody';
    public const FOR_NAME = 'tr > td:nth-child(3) > a';
    public const FOR_INFORMATION = 'tr > td:nth-child(5)';
    public const FOR_ADDRESS = 'tr > td:nth-child(3) > a';
    public const FOR_HOLDERS = '#ContentPlaceHolder1_tr_tokenHolders > div > div.col-md-8 > div > div';
}
