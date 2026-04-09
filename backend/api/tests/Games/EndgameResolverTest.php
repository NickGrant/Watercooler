<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Games;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Games\ActiveGamePlayer;
use Watercooler\Api\Games\ActiveGameState;
use Watercooler\Api\Games\EndgameResolver;
use Watercooler\Api\Games\PlayerCardView;
use Watercooler\Api\Games\PlayerResourceSet;

final class EndgameResolverTest extends TestCase
{
    public function testItTriggersEndgameWhenTheActingPlayerReachesTheTargetPrestige(): void
    {
        $resolver = new EndgameResolver();
        $state = new ActiveGameState(
            players: [
                new ActiveGamePlayer(1, 'Pam', true, 'connected', 1, 15, new PlayerResourceSet(0, 0, 0, 0, 0, 0)),
                new ActiveGamePlayer(2, 'Jim', false, 'connected', 2, 8, new PlayerResourceSet(0, 0, 0, 0, 0, 0)),
            ],
            currentTurnGamePlayerId: 2,
            bank: [
                'coffee' => 4,
                'spreadsheets' => 4,
                'budget' => 4,
                'connections' => 4,
                'time' => 4,
                'executiveFavor' => 5,
            ],
            marketCardsByTier: [1 => [], 2 => [], 3 => []],
            executives: [],
        );

        self::assertTrue($resolver->shouldTriggerEndgame($state, 1));
        self::assertFalse($resolver->isLastSeat($state, 1));
        self::assertTrue($resolver->isLastSeat($state, 2));
    }

    public function testItResolvesTiesByFewestPurchasedCardsThenSeatOrder(): void
    {
        $resolver = new EndgameResolver();
        $state = new ActiveGameState(
            players: [
                new ActiveGamePlayer(
                    1,
                    'Pam',
                    true,
                    'connected',
                    1,
                    16,
                    new PlayerResourceSet(0, 0, 0, 0, 0, 0),
                    purchasedCards: [purchasedCard('p1'), purchasedCard('p2'), purchasedCard('p3')],
                ),
                new ActiveGamePlayer(
                    2,
                    'Jim',
                    false,
                    'connected',
                    2,
                    16,
                    new PlayerResourceSet(0, 0, 0, 0, 0, 0),
                    purchasedCards: [purchasedCard('j1'), purchasedCard('j2')],
                ),
                new ActiveGamePlayer(
                    3,
                    'Dwight',
                    false,
                    'connected',
                    3,
                    16,
                    new PlayerResourceSet(0, 0, 0, 0, 0, 0),
                    purchasedCards: [purchasedCard('d1'), purchasedCard('d2')],
                ),
            ],
            currentTurnGamePlayerId: 1,
            bank: [
                'coffee' => 4,
                'spreadsheets' => 4,
                'budget' => 4,
                'connections' => 4,
                'time' => 4,
                'executiveFavor' => 5,
            ],
            marketCardsByTier: [1 => [], 2 => [], 3 => []],
            executives: [],
        );

        $winner = $resolver->resolveWinner($state);

        self::assertSame(2, $winner->winnerGamePlayerId);
        self::assertSame([2, 3], $winner->tiedGamePlayerIds);
        self::assertSame(16, $winner->winningPrestige);
        self::assertSame(2, $winner->winningPurchasedCardCount);
    }
}

function purchasedCard(string $code): PlayerCardView
{
    return new PlayerCardView($code, 1, 'Purchased Card', 'coffee', 0, [
        'coffee' => 0,
        'spreadsheets' => 0,
        'budget' => 0,
        'connections' => 0,
        'time' => 0,
    ]);
}
