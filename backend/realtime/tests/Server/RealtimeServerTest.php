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
}
