<?php

use App\Application\ApplicationProcess;

require_once '/mnt/app/vendor/autoload.php';


$process = new ApplicationProcess();
$process->invoke(1,10);

