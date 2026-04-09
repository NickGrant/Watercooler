<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Tests\Rooms;

use PHPUnit\Framework\TestCase;
use Watercooler\Realtime\Rooms\GameRoom;
use Watercooler\Realtime\Sessions\ClientSession;

final class GameRoomTest extends TestCase
{
    public function testItReplacesThePreviousConnectionWhenTheSamePlayerReconnects(): void
    {
        $room = new GameRoom('synergy-report-telemetry');
        $first = new ClientSession('conn-1', 'synergy-report-telemetry', 'player-1', 'game-player-1');
        $second = new ClientSession('conn-2', 'synergy-report-telemetry', 'player-1', 'game-player-1');

        self::assertNull($room->addSession($first));
        $replaced = $room->addSession($second);

        self::assertSame('conn-1', $replaced?->connectionId);
        self::assertSame('conn-2', $room->snapshot()['connections'][0]['connectionId']);
    }
}
