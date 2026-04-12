<?php

declare(strict_types=1);

use Watercooler\Api\Config\AppConfig;
use Watercooler\Api\Config\Env;
use Watercooler\Api\Database\PdoStaleGamePurgeRepository;
use Watercooler\Api\Maintenance\StaleGamePurgeService;

require dirname(__DIR__) . '/vendor/autoload.php';

$config = AppConfig::fromEnv(new Env());
$service = new StaleGamePurgeService(
    new PdoStaleGamePurgeRepository($config->database),
);
$result = $service->purgeInactiveGames(new DateTimeImmutable('now'));

fwrite(STDOUT, sprintf(
    "[watercooler] Purged %d stale game(s) last updated before %s.\n",
    $result->purgedGameCount,
    $result->cutoff->format('Y-m-d H:i:s')
));

if ($result->slugs !== []) {
    fwrite(STDOUT, sprintf(
        "[watercooler] Removed rooms: %s\n",
        implode(', ', $result->slugs)
    ));
}
