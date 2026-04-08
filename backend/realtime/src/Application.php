<?php

declare(strict_types=1);

namespace Watercooler\Realtime;

use Watercooler\Realtime\Config\AppConfig;
use Watercooler\Realtime\Config\Env;
use Watercooler\Realtime\Database\PdoJoinRoomRepository;
use Watercooler\Realtime\Lobby\RoomJoinService;
use Watercooler\Realtime\Rooms\ActiveRoomRegistry;
use Watercooler\Realtime\Server\RealtimeServer;
use Watercooler\Realtime\Support\ConsoleLogger;

final class Application
{
    private function __construct(
        private readonly AppConfig $config,
        private readonly RealtimeServer $server,
    ) {
    }

    public static function boot(string $basePath): self
    {
        $config = AppConfig::fromEnv(new Env());
        $logger = new ConsoleLogger($config->debug);
        $roomRegistry = new ActiveRoomRegistry();
        $joinRoomRepository = new PdoJoinRoomRepository($config->database);
        $joinRoomService = new RoomJoinService($joinRoomRepository, $roomRegistry);
        $server = new RealtimeServer($config, $logger, $roomRegistry, $joinRoomService);

        return new self($config, $server);
    }

    public function run(bool $runOnce = false): void
    {
        $this->server->run($runOnce);
    }
}
