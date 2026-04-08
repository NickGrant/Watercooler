<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Server;

use Watercooler\Realtime\Config\AppConfig;
use Watercooler\Realtime\Lobby\RoomJoinService;
use Watercooler\Realtime\Rooms\ActiveRoomRegistry;
use Watercooler\Realtime\Support\Logger;

final class RealtimeServer
{
    public function __construct(
        private readonly AppConfig $config,
        private readonly Logger $logger,
        private readonly ActiveRoomRegistry $roomRegistry,
        private readonly RoomJoinService $joinRoomService,
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
            $this->logger->info('Bootstrap room registry preview generated.', [
                'rooms' => [
                    'preview' => [
                        'instructions' => 'Use joinConnection() after HTTP join-bootstrap succeeds.',
                    ],
                ],
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

    /**
     * @return array<string, mixed>
     */
    public function joinConnection(string $connectionId, string $slug, string $sessionToken): array
    {
        $result = $this->joinRoomService->join($connectionId, $slug, $sessionToken);
        $roomSnapshot = $this->roomRegistry->snapshot();

        $this->logger->info('Realtime connection joined room.', [
            'connectionId' => $connectionId,
            'gameSlug' => $slug,
            'playerId' => $result->participant->playerId,
            'connectedPlayers' => count($result->participants),
        ]);

        return [
            'event' => 'lobby.presence.sync',
            'room' => $roomSnapshot[$slug] ?? null,
            'payload' => $result->toArray(),
        ];
    }

    public function disconnectConnection(string $connectionId): void
    {
        $session = $this->joinRoomService->disconnect($connectionId);

        if ($session === null) {
            return;
        }

        $this->logger->info('Realtime connection disconnected from room.', [
            'connectionId' => $connectionId,
            'gameSlug' => $session->gameSlug,
            'activeRooms' => count($this->roomRegistry->snapshot()),
        ]);
    }
}
