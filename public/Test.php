<?php

use App\Application\PantherService;
use App\Domain\ValueObjects\Url;
use App\Infrastructure\RouterTransactionFactory;
use Facebook\WebDriver\Remote\RemoteWebElement;

require_once './vendor/autoload.php';

$panther = new PantherService();
$factory = new RouterTransactionFactory();
// #myTabContent
$panther->saveWebElements(Url::fromString('https://bscscan.com/tx/0xb976fa9bd2e57994bfc3122c8fa86f4e3b913d2ec463c9c57abd7865c3a8d0da'));
$elements = $panther->savedWebElements();
$count = count($elements);
foreach ($elements as $element) {
    assert($element instanceof RemoteWebElement);
    $tokenAddress = $factory->findSoldTokens($element, $count);
    var_dump($tokenAddress);
}