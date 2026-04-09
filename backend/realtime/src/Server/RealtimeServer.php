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
        $event = $result->wasReconnect ? 'lobby.presence.resync' : 'lobby.presence.sync';

        $this->logger->info('Realtime connection joined room.', [
            'connectionId' => $connectionId,
            'gameSlug' => $slug,
            'playerId' => $result->participant->playerId,
            'connectedPlayers' => count($result->participants),
            'wasReconnect' => $result->wasReconnect,
            'replacedConnectionId' => $result->replacedConnectionId,
        ]);

        return [
            'event' => $event,
            'room' => $roomSnapshot[$slug] ?? null,
            'payload' => $result->toArray(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function disconnectConnection(string $connectionId): array
    {
        $session = $this->joinRoomService->disconnect($connectionId);
        $roomSnapshot = $this->roomRegistry->snapshot();

        if ($session === null) {
            $this->logger->debug('Realtime disconnect ignored for unknown connection.', [
                'connectionId' => $connectionId,
            ]);

            return [
                'event' => 'lobby.presence.disconnect.missed',
                'room' => null,
                'payload' => [
                    'connectionId' => $connectionId,
                    'wasKnownConnection' => false,
                ],
            ];
        }

        $room = $roomSnapshot[$session->gameSlug] ?? null;

        $this->logger->info('Realtime connection disconnected from room.', [
            'connectionId' => $connectionId,
            'gameSlug' => $session->gameSlug,
            'activeRooms' => count($roomSnapshot),
            'remainingConnections' => $room !== null ? count($room['connections']) : 0,
            'roomEmptied' => $room === null,
        ]);

        return [
            'event' => 'lobby.presence.disconnect',
            'room' => $room,
            'payload' => [
                'connectionId' => $connectionId,
                'session' => $session->toArray(),
                'wasKnownConnection' => true,
                'roomEmptied' => $room === null,
                'remainingConnections' => $room !== null ? count($room['connections']) : 0,
            ],
        ];
    }
}
