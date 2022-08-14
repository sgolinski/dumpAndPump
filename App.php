<?php

use App\Application\ApplicationProcess;

require_once 'vendor/autoload.php';

$process = new ApplicationProcess();

$process->invoke(1,100);
$process->processEvents();
