<?php

use App\Application\PantherService;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

require_once './vendor/autoload.php';

$panther = new PantherService();
//https://honeypot.is/?address=0x57b798d2252557f13a9148a075a72816f2707356
$panther->getClient()->get("https://tokensniffer.com/token/0xdd26a25f872d87da9f0de9652085bcc8c1923ddc");
sleep(1);

$panther->getClient()->getWebDriver()->manage()->addCookie(\Facebook\WebDriver\Cookie::createFromArray(["name" => "cf_clearance", "value" => "yaPxF9efPIO18_e2QoanpuPatmexFUWh__qo6r7cvp4-1662204329-0-150"]));
$panther->getClient()->wait()->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('__next'))
);
var_dump($panther->getClient()->getCrawler()->getText());
$panther->getClient()->takeScreenshot('check.png');
