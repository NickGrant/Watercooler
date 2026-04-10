<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Server;

use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\SocketServer;
use Watercooler\Realtime\Config\AppConfig;
use Watercooler\Realtime\Lobby\RoomJoinService;
use Watercooler\Realtime\Rooms\ActiveRoomRegistry;
use Watercooler\Realtime\State\GameStateFetcher;
use Watercooler\Realtime\Support\Logger;

final class RealtimeServer
{
    /** @var array<string, callable(array<string, mixed>): void> */
    private array $connectionSenders = [];

    /** @var array<string, string> */
    private array $connectionTokens = [];

    /** @var array<string, string> */
    private array $connectionRooms = [];

    /** @var array<string, string> */
    private array $roomHashes = [];

    public function __construct(
        private readonly AppConfig $config,
        private readonly Logger $logger,
        private readonly ActiveRoomRegistry $roomRegistry,
        private readonly RoomJoinService $joinRoomService,
        private readonly GameStateFetcher $stateFetcher,
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

        $loop = LoopFactory::create();
        $component = new class($this) implements MessageComponentInterface
        {
            public function __construct(
                private readonly RealtimeServer $server,
            ) {
            }

            public function onOpen(ConnectionInterface $connection): void
            {
                $this->server->registerTransportConnection((string) $connection->resourceId, $connection);
            }

            public function onMessage(ConnectionInterface $from, $message): void
            {
                $this->server->handleIncomingMessage((string) $from->resourceId, (string) $message);
            }

            public function onClose(ConnectionInterface $connection): void
            {
                $this->server->disconnectTransportConnection((string) $connection->resourceId);
            }

            public function onError(ConnectionInterface $connection, \Exception $exception): void
            {
                $this->server->disconnectTransportConnection((string) $connection->resourceId);
                $connection->close();
            }
        };

        $socket = new SocketServer(
            sprintf('%s:%d', $this->config->host, $this->config->port),
            [],
            $loop,
        );
        new IoServer(new HttpServer(new WsServer($component)), $socket, $loop);

        $loop->addPeriodicTimer($this->config->syncIntervalMs / 1000, function (): void {
            $this->syncActiveRooms();
        });

        $this->logger->info('Realtime websocket transport listening.', [
            'host' => $this->config->host,
            'port' => $this->config->port,
            'syncIntervalMs' => $this->config->syncIntervalMs,
        ]);

        $loop->run();
    }

    /**
     * @return array<string, mixed>
     */
    public function joinConnection(string $connectionId, string $slug, string $sessionToken): array
    {
        $result = $this->joinRoomService->join($connectionId, $slug, $sessionToken);
        $roomSnapshot = $this->roomRegistry->snapshot();
        $event = $result->wasReconnect ? 'lobby.presence.resync' : 'lobby.presence.sync';

        $this->connectionTokens[$connectionId] = $sessionToken;
        $this->connectionRooms[$connectionId] = $slug;

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
        unset($this->connectionTokens[$connectionId], $this->connectionSenders[$connectionId]);

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

        unset($this->connectionRooms[$connectionId]);
        $room = $roomSnapshot[$session->gameSlug] ?? null;
        if ($room === null) {
            unset($this->roomHashes[$session->gameSlug]);
        }

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

    public function registerTransportConnection(string $connectionId, ConnectionInterface $connection): void
    {
        $this->registerTransportSender($connectionId, static function (array $payload) use ($connection): void {
            $connection->send((string) json_encode($payload, JSON_THROW_ON_ERROR));
        });
    }

    public function registerTransportSender(string $connectionId, callable $sender): void
    {
        $this->connectionSenders[$connectionId] = $sender;
    }

    public function handleIncomingMessage(string $connectionId, string $message): void
    {
        $decoded = json_decode($message, true);

        if (!is_array($decoded)) {
            $this->sendToConnection($connectionId, [
                'event' => 'realtime.error',
                'payload' => [
                    'message' => 'Realtime messages must be valid JSON objects.',
                ],
            ]);
            return;
        }

        if (($decoded['type'] ?? null) !== 'join') {
            $this->sendToConnection($connectionId, [
                'event' => 'realtime.error',
                'payload' => [
                    'message' => 'Unsupported realtime message type.',
                ],
            ]);
            return;
        }

        $slug = trim((string) ($decoded['slug'] ?? ''));
        $sessionToken = trim((string) ($decoded['sessionToken'] ?? ''));

        if ($slug === '' || $sessionToken === '') {
            $this->sendToConnection($connectionId, [
                'event' => 'realtime.error',
                'payload' => [
                    'message' => 'Realtime join requires both slug and session token.',
                ],
            ]);
            return;
        }

        try {
            $joinPayload = $this->joinConnection($connectionId, $slug, $sessionToken);
        } catch (\Throwable $exception) {
            $this->sendToConnection($connectionId, [
                'event' => 'realtime.error',
                'payload' => [
                    'message' => $exception->getMessage(),
                ],
            ]);
            return;
        }

        $this->sendToConnection($connectionId, [
            'event' => 'realtime.joined',
            'payload' => $joinPayload['payload'],
        ]);

        if (($joinPayload['payload']['reconnect']['wasReconnect'] ?? false) === true) {
            $replacedConnectionId = (string) ($joinPayload['payload']['reconnect']['replacedConnectionId'] ?? '');

            if ($replacedConnectionId !== '' && isset($this->connectionSenders[$replacedConnectionId])) {
                $this->sendToConnection($replacedConnectionId, [
                    'event' => 'realtime.error',
                    'payload' => [
                        'message' => 'A newer connection replaced this session.',
                    ],
                ]);
            }
        }

        $this->syncRoomState($slug);
    }

    public function disconnectTransportConnection(string $connectionId): void
    {
        $payload = $this->disconnectConnection($connectionId);

        if (($payload['event'] ?? null) === 'lobby.presence.disconnect' && isset($payload['payload']['session']['gameSlug'])) {
            $this->syncRoomState((string) $payload['payload']['session']['gameSlug']);
        }
    }

    public function syncActiveRooms(): void
    {
        foreach (array_keys($this->roomRegistry->snapshot()) as $slug) {
            $this->syncRoomState($slug);
        }

        $this->logger->debug('Realtime transport heartbeat.', [
            'activeRooms' => count($this->roomRegistry->snapshot()),
        ]);
    }

    private function syncRoomState(string $slug): void
    {
        $sessionToken = $this->representativeSessionToken($slug);

        if ($sessionToken === null) {
            return;
        }

        $payload = $this->stateFetcher->fetch($slug, $sessionToken);
        if ($payload === null) {
            return;
        }

        $hash = sha1((string) json_encode($payload));
        if (($this->roomHashes[$slug] ?? null) === $hash) {
            return;
        }

        $this->roomHashes[$slug] = $hash;
        $this->broadcastToRoom($slug, [
            'event' => 'game.state.sync',
            'payload' => $payload,
        ]);
    }

    private function representativeSessionToken(string $slug): ?string
    {
        foreach ($this->connectionRooms as $connectionId => $roomSlug) {
            if ($roomSlug === $slug && isset($this->connectionTokens[$connectionId])) {
                return $this->connectionTokens[$connectionId];
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function sendToConnection(string $connectionId, array $payload): void
    {
        ($this->connectionSenders[$connectionId] ?? static function (): void {
        })($payload);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function broadcastToRoom(string $slug, array $payload): void
    {
        foreach ($this->connectionRooms as $connectionId => $roomSlug) {
            if ($roomSlug === $slug) {
                $this->sendToConnection($connectionId, $payload);
            }
        }
    }
}
