<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Database;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Watercooler\Api\Config\DatabaseConfig;
use Watercooler\Api\Database\PdoTakeResourcesRepository;
use Watercooler\Api\Games\ActiveGamePlayer;
use Watercooler\Api\Games\ActiveGameState;
use Watercooler\Api\Games\PlayerResourceSet;

final class PdoTakeResourcesRepositoryTest extends TestCase
{
    public function testItDeterminesTheNextPlayerFromSeatOrderWithoutTypeErrors(): void
    {
        $repository = new PdoTakeResourcesRepository(
            new DatabaseConfig('127.0.0.1', 3306, 'watercooler', 'watercooler', 'watercooler'),
        );
        $method = new ReflectionMethod($repository, 'nextPlayerId');
        $method->setAccessible(true);

        $nextPlayerId = $method->invoke(
            $repository,
            new ActiveGameState(
                players: [
                    $this->makePlayer(gamePlayerId: 10, seatOrder: 3),
                    $this->makePlayer(gamePlayerId: 11, seatOrder: 1),
                    $this->makePlayer(gamePlayerId: 12, seatOrder: 2),
                ],
                currentTurnGamePlayerId: 12,
                bank: [],
                marketCardsByTier: [],
                executives: [],
            ),
            12,
        );

        self::assertSame(10, $nextPlayerId);
    }

    private function makePlayer(int $gamePlayerId, int $seatOrder): ActiveGamePlayer
    {
        return new ActiveGamePlayer(
            gamePlayerId: $gamePlayerId,
            displayName: 'Player ' . $gamePlayerId,
            isHost: $seatOrder === 1,
            joinStatus: 'connected',
            seatOrder: $seatOrder,
            officePrestige: 0,
            resources: new PlayerResourceSet(0, 0, 0, 0, 0, 0),
        );
    }
}
