<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Tests\Server;

use PHPUnit\Framework\TestCase;
use Watercooler\Realtime\Config\AppConfig;
use Watercooler\Realtime\Config\DatabaseConfig;
use Watercooler\Realtime\Lobby\RoomJoinService;
use Watercooler\Realtime\Rooms\ActiveRoomRegistry;
use Watercooler\Realtime\Server\RealtimeServer;
use Watercooler\Realtime\Tests\Lobby\InMemoryJoinRoomRepository;
use Watercooler\Realtime\Tests\Support\InMemoryLogger;

final class RealtimeServerTest extends TestCase
{
    public function testRunOnceLogsBootstrapPreviewAndPopulatesTheRegistry(): void
    {
        $logger = new InMemoryLogger();
        $registry = new ActiveRoomRegistry();
        $server = new RealtimeServer(
            new AppConfig('development', true, '0.0.0.0', 8090, new DatabaseConfig('db', 3306, 'watercooler', 'root', '')),
            $logger,
            $registry,
            new RoomJoinService(new InMemoryJoinRoomRepository(), $registry),
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
            new AppConfig('development', true, '0.0.0.0', 8090, new DatabaseConfig('db', 3306, 'watercooler', 'root', '')),
            $logger,
            $registry,
            new RoomJoinService(new InMemoryJoinRoomRepository(), $registry),
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
            new AppConfig('development', true, '0.0.0.0', 8090, new DatabaseConfig('db', 3306, 'watercooler', 'root', '')),
            $logger,
            $registry,
            new RoomJoinService(new InMemoryJoinRoomRepository(), $registry),
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
            new AppConfig('development', true, '0.0.0.0', 8090, new DatabaseConfig('db', 3306, 'watercooler', 'root', '')),
            $logger,
            $registry,
            new RoomJoinService(new InMemoryJoinRoomRepository(), $registry),
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
            new AppConfig('development', true, '0.0.0.0', 8090, new DatabaseConfig('db', 3306, 'watercooler', 'root', '')),
            $logger,
            $registry,
            new RoomJoinService(new InMemoryJoinRoomRepository(), $registry),
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
            new AppConfig('development', true, '0.0.0.0', 8090, new DatabaseConfig('db', 3306, 'watercooler', 'root', '')),
            $logger,
            $registry,
            new RoomJoinService(new InMemoryJoinRoomRepository(), $registry),
        );

        $payload = $server->disconnectConnection('missing-conn');

        self::assertSame('lobby.presence.disconnect.missed', $payload['event']);
        self::assertFalse($payload['payload']['wasKnownConnection']);
        self::assertNull($payload['room']);
    }
}
