<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Tests\Server;

use PHPUnit\Framework\TestCase;
use Watercooler\Realtime\Config\AppConfig;
use Watercooler\Realtime\Config\DatabaseConfig;
use Watercooler\Realtime\Lobby\RoomJoinService;
use Watercooler\Realtime\Rooms\ActiveRoomRegistry;
use Watercooler\Realtime\Server\RealtimeServer;
use Watercooler\Realtime\State\GameStateFetcher;
use Watercooler\Realtime\Tests\Lobby\InMemoryJoinRoomRepository;
use Watercooler\Realtime\Tests\Support\InMemoryLogger;

final class RealtimeServerTest extends TestCase
{
    public function testRunOnceLogsBootstrapPreviewAndPopulatesTheRegistry(): void
    {
        $logger = new InMemoryLogger();
        $registry = new ActiveRoomRegistry();
        $server = new RealtimeServer(
            $this->config(),
            $logger,
            $registry,
            new RoomJoinService(new InMemoryJoinRoomRepository(), $registry),
            new InMemoryGameStateFetcher(),
        );

        $server->run(true);

        self::assertCount(3, $logger->entries);
        self::assertSame('Realtime service scaffold booted.', $logger->entries[0]['message']);
        self::assertSame([], $registry->snapshot());
    }

    public function testItJoinsAConnectionAndReturnsPresenceSyncPayload(): void
    {
        $logger = new InMemoryLogger();
        $registry = new ActiveRoomRegistry();
        $server = new RealtimeServer(
            $this->config(),
            $logger,
            $registry,
            new RoomJoinService(new InMemoryJoinRoomRepository(), $registry),
            new InMemoryGameStateFetcher(),
        );

        $payload = $server->joinConnection('conn-1', 'synergy-report-telemetry', 'temporary-session-token');

        self::assertSame('lobby.presence.sync', $payload['event']);
        self::assertSame('conn-1', $payload['room']['connections'][0]['connectionId']);
        self::assertSame('Pam', $payload['payload']['participant']['displayName']);
    }

    public function testItReturnsAReconnectResyncPayloadWhenTheSamePlayerReconnects(): void
    {
        $logger = new InMemoryLogger();
        $registry = new ActiveRoomRegistry();
        $server = new RealtimeServer(
            $this->config(),
            $logger,
            $registry,
            new RoomJoinService(new InMemoryJoinRoomRepository(), $registry),
            new InMemoryGameStateFetcher(),
        );

        $server->joinConnection('conn-1', 'synergy-report-telemetry', 'temporary-session-token');
        $payload = $server->joinConnection('conn-2', 'synergy-report-telemetry', 'temporary-session-token');

        self::assertSame('lobby.presence.resync', $payload['event']);
        self::assertTrue($payload['payload']['reconnect']['wasReconnect']);
        self::assertSame('conn-1', $payload['payload']['reconnect']['replacedConnectionId']);
    }

    public function testItReturnsADisconnectPayloadAndRoomSnapshotWhenAConnectionCloses(): void
    {
        $logger = new InMemoryLogger();
        $registry = new ActiveRoomRegistry();
        $server = new RealtimeServer(
            $this->config(),
            $logger,
            $registry,
            new RoomJoinService(new InMemoryJoinRoomRepository(), $registry),
            new InMemoryGameStateFetcher(),
        );

        $server->joinConnection('conn-1', 'synergy-report-telemetry', 'temporary-session-token');
        $payload = $server->disconnectConnection('conn-1');

        self::assertSame('lobby.presence.disconnect', $payload['event']);
        self::assertTrue($payload['payload']['wasKnownConnection']);
        self::assertTrue($payload['payload']['roomEmptied']);
        self::assertNull($payload['room']);
    }

    public function testItPreservesTheRemainingRoomSnapshotWhenOneConnectionDisconnects(): void
    {
        $logger = new InMemoryLogger();
        $registry = new ActiveRoomRegistry();
        $server = new RealtimeServer(
            $this->config(),
            $logger,
            $registry,
            new RoomJoinService(new InMemoryJoinRoomRepository(), $registry),
            new InMemoryGameStateFetcher(),
        );

        $server->joinConnection('conn-1', 'synergy-report-telemetry', 'temporary-session-token');
        $server->joinConnection('conn-2', 'synergy-report-telemetry', 'second-token');
        $payload = $server->disconnectConnection('conn-1');

        self::assertSame('lobby.presence.disconnect', $payload['event']);
        self::assertFalse($payload['payload']['roomEmptied']);
        self::assertSame('conn-2', $payload['room']['connections'][0]['connectionId']);
        self::assertSame(1, $payload['payload']['remainingConnections']);
    }

    public function testItGracefullyIgnoresUnknownDisconnects(): void
    {
        $logger = new InMemoryLogger();
        $registry = new ActiveRoomRegistry();
        $server = new RealtimeServer(
            $this->config(),
            $logger,
            $registry,
            new RoomJoinService(new InMemoryJoinRoomRepository(), $registry),
            new InMemoryGameStateFetcher(),
        );

        $payload = $server->disconnectConnection('missing-conn');

        self::assertSame('lobby.presence.disconnect.missed', $payload['event']);
        self::assertFalse($payload['payload']['wasKnownConnection']);
        self::assertNull($payload['room']);
    }

    public function testItBroadcastsAuthoritativeStateSnapshotsWhenRoomsChange(): void
    {
        $logger = new InMemoryLogger();
        $registry = new ActiveRoomRegistry();
        $fetcher = new InMemoryGameStateFetcher();
        $server = new RealtimeServer(
            $this->config(),
            $logger,
            $registry,
            new RoomJoinService(new InMemoryJoinRoomRepository(), $registry),
            $fetcher,
        );
        $messages = [];

        $server->registerTransportSender('conn-1', static function (array $payload) use (&$messages): void {
            $messages[] = $payload;
        });
        $server->joinConnection('conn-1', 'synergy-report-telemetry', 'temporary-session-token');

        $server->syncActiveRooms();
        $server->syncActiveRooms();
        $fetcher->payload['state']['currentTurnGamePlayerId'] = 2;
        $server->syncActiveRooms();

        self::assertCount(2, $messages);
        self::assertSame('game.state.sync', $messages[0]['event']);
        self::assertSame(1, $messages[0]['payload']['state']['currentTurnGamePlayerId']);
        self::assertSame(2, $messages[1]['payload']['state']['currentTurnGamePlayerId']);
    }

    private function config(): AppConfig
    {
        return new AppConfig(
            'development',
            true,
            '0.0.0.0',
            8090,
            'http://api:8080',
            1000,
            new DatabaseConfig('db', 3306, 'watercooler', 'root', ''),
        );
    }
}

final class InMemoryGameStateFetcher implements GameStateFetcher
{
    /** @var array<string, mixed> */
    public array $payload = [
        'game' => [
            'id' => 1,
            'slug' => 'synergy-report-telemetry',
            'status' => 'active',
            'phase' => 'active',
            'playerCount' => 2,
            'createdAt' => '2026-04-08 00:00:00',
            'path' => '/game/synergy-report-telemetry',
        ],
        'player' => [
            'gamePlayerId' => 1,
            'playerId' => 101,
            'displayName' => 'Pam',
            'isHost' => true,
            'joinStatus' => 'connected',
            'avatar' => [
                'body' => 'blazer',
                'face' => 'corporate-neutral',
                'hair' => 'side-part',
            ],
        ],
        'session' => [
            'token' => 'temporary-session-token',
            'reconnectEnabled' => true,
        ],
        'realtime' => [
            'transport' => 'websocket',
            'roomSlug' => 'synergy-report-telemetry',
            'sessionToken' => 'temporary-session-token',
        ],
        'state' => [
            'currentTurnGamePlayerId' => 1,
            'players' => [],
            'bank' => [
                'coffee' => 4,
                'spreadsheets' => 4,
                'budget' => 4,
                'connections' => 4,
                'time' => 4,
                'executiveFavor' => 5,
            ],
            'market' => [
                'tier1' => [],
                'tier2' => [],
                'tier3' => [],
            ],
            'executives' => [],
        ],
    ];

    public function fetch(string $slug, string $sessionToken): ?array
    {
        return $slug === 'synergy-report-telemetry' && $sessionToken === 'temporary-session-token'
            ? $this->payload
            : null;
    }
}
