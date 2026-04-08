<?php

declare(strict_types=1);

use Watercooler\Realtime\Application;

require dirname(__DIR__) . '/vendor/autoload.php';

$application = Application::boot(dirname(__DIR__));
$application->run(in_array('--once', $argv, true));
