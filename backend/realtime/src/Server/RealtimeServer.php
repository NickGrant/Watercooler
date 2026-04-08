<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Server;

use Watercooler\Realtime\Config\AppConfig;
use Watercooler\Realtime\Rooms\ActiveRoomRegistry;
use Watercooler\Realtime\Sessions\ClientSession;
use Watercooler\Realtime\Support\Logger;

final class RealtimeServer
{
    public function __construct(
        private readonly AppConfig $config,
        private readonly Logger $logger,
        private readonly ActiveRoomRegistry $roomRegistry,
    ) {
    }

    public function run(bool $runOnce = false): void
    {
        $this->logger->info('Realtime service scaffold booted.', [
            'host' => $this->config->host,
            'port' => $this->config->port,
            'environment' => $this->config->environment,
            'mode' => $runOnce ? 'bootstrap-check' : 'service-shell',
        ]);

        $this->logger->info('Planned connection flow is wired to room and session primitives.', [
            'steps' => [
                'accept websocket transport connection',
                'authenticate player session after join-bootstrap',
                'bind connection to game room slug',
                'broadcast authoritative state updates',
            ],
        ]);

        if ($runOnce) {
            $exampleSession = new ClientSession('bootstrap-preview', 'synergy-report-telemetry');
            $this->roomRegistry->addConnection($exampleSession->gameSlug, $exampleSession);

            $this->logger->info('Bootstrap room registry preview generated.', [
                'rooms' => $this->roomRegistry->snapshot(),
            ]);

            return;
        }

        while (true) {
            sleep(5);
            $this->logger->debug('Realtime scaffold heartbeat.', [
                'activeRooms' => count($this->roomRegistry->snapshot()),
            ]);
        }
    }
}
