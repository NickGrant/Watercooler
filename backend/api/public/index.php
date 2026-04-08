<?php

declare(strict_types=1);

use Watercooler\Api\Application;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = Application::boot(dirname(__DIR__));
$app->handle()->send();
