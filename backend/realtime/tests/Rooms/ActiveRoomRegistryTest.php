<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Tests\Rooms;

use PHPUnit\Framework\TestCase;
use Watercooler\Realtime\Rooms\ActiveRoomRegistry;
use Watercooler\Realtime\Sessions\ClientSession;

final class ActiveRoomRegistryTest extends TestCase
{
    public function testItCreatesRoomsAndTracksConnections(): void
    {
        $registry = new ActiveRoomRegistry();
        $session = new ClientSession('conn-1', 'synergy-report-telemetry', 'player-1', 'game-player-1');

        $registry->addConnection('synergy-report-telemetry', $session);
        $snapshot = $registry->snapshot();

        self::assertArrayHasKey('synergy-report-telemetry', $snapshot);
        self::assertSame('conn-1', $snapshot['synergy-report-telemetry']['connections'][0]['connectionId']);
    }

    public function testItRemovesConnectionsAndCleansUpEmptyRooms(): void
    {
        $registry = new ActiveRoomRegistry();
        $registry->addConnection(
            'synergy-report-telemetry',
            new ClientSession('conn-1', 'synergy-report-telemetry', 'player-1', 'game-player-1'),
        );

        $removed = $registry->removeConnection('conn-1');

        self::assertSame('conn-1', $removed?->connectionId);
        self::assertSame([], $registry->snapshot());
    }
}
