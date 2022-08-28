<?php

use App\Application\PantherService;

require_once './vendor/autoload.php';

$panther = new PantherService();

$res  = $panther->getClient()->get('https://www.coingecko.com/en/coins/monstedssra');

var_dump($res->getCrawler()->filter('body')->getAttribute('data-action-name'));