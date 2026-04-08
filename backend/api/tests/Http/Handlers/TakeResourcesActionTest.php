<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Http\Handlers;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Games\ActiveGamePlayer;
use Watercooler\Api\Games\ActiveGameState;
use Watercooler\Api\Games\CardSeedDefinition;
use Watercooler\Api\Games\ExecutiveSeedDefinition;
use Watercooler\Api\Games\GameSummary;
use Watercooler\Api\Games\PlayerResourceSet;
use Watercooler\Api\Games\StartGamePlayer;
use Watercooler\Api\Games\TakeResourcesRepository;
use Watercooler\Api\Games\TakeResourcesService;
use Watercooler\Api\Http\Handlers\TakeResourcesAction;
use Watercooler\Api\Http\Request;
use Watercooler\Api\Http\Routing\RouteMatch;

final class TakeResourcesActionTest extends TestCase
{
    public function testItReturnsTheUpdatedGameState(): void
    {
        $action = new TakeResourcesAction(
            new TakeResourcesService(new HandlerTakeResourcesRepository()),
        );

        $response = $action(
            new Request(
                'POST',
                '/api/games/synergy-report-telemetry/take-resources',
                [],
                [],
                [
                    'sessionToken' => 'host-token',
                    'resources' => ['coffee', 'budget', 'time'],
                ],
            ),
            new RouteMatch(static fn() => null, ['slug' => 'synergy-report-telemetry']),
        );

        self::assertSame(200, $response->statusCode);
        self::assertStringContainsString('"currentTurnGamePlayerId": 2', $response->body);
        self::assertStringContainsString('"totalTokens": 3', $response->body);
    }

    public function testItReturnsValidationErrorsAsJson(): void
    {
        $action = new TakeResourcesAction(
            new TakeResourcesService(new HandlerTakeResourcesRepository()),
        );

        $response = $action(
            new Request(
                'POST',
                '/api/games/synergy-report-telemetry/take-resources',
                [],
                [],
                [
                    'sessionToken' => 'host-token',
                    'resources' => ['coffee', 'coffee', 'budget'],
                ],
            ),
            new RouteMatch(static fn() => null, ['slug' => 'synergy-report-telemetry']),
        );

        self::assertSame(422, $response->statusCode);
        self::assertStringContainsString('three_take_requires_distinct_resources', $response->body);
    }
}

final class HandlerTakeResourcesRepository implements TakeResourcesRepository
{
    public function findGameBySlug(string $slug): ?GameSummary
    {
        return $slug === 'synergy-report-telemetry'
            ? new GameSummary(1, 'synergy-report-telemetry', 'active', 'active', 2, '2026-04-08 00:00:00')
            : null;
    }

    public function findPlayerBySessionToken(int $gameId, string $sessionTokenHash): ?StartGamePlayer
    {
        return $sessionTokenHash === hash('sha256', 'host-token')
            ? new StartGamePlayer(1, 'Pam', true, 'connected', 1, 0)
            : null;
    }

    public function loadState(int $gameId): ActiveGameState
    {
        return new ActiveGameState(
            players: [
                new ActiveGamePlayer(1, 'Pam', true, 'connected', 1, 0, new PlayerResourceSet(0, 0, 0, 0, 0, 0)),
                new ActiveGamePlayer(2, 'Jim', false, 'connected', 2, 0, new PlayerResourceSet(0, 0, 0, 0, 0, 0)),
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
            marketCardsByTier: [
                1 => [
                    new CardSeedDefinition('t1-01', 1, 'Coffee Workflow 01', 'coffee', 0, ['coffee' => 0, 'spreadsheets' => 1, 'budget' => 1, 'connections' => 1, 'time' => 1], 1),
                ],
                2 => [],
                3 => [],
            ],
            executives: [
                new ExecutiveSeedDefinition('vp-of-synergy', 'VP of Synergy', 3, ['coffee' => 3, 'spreadsheets' => 0, 'budget' => 3, 'connections' => 3, 'time' => 0]),
            ],
        );
    }

    public function applyTakeResources(
        int $gameId,
        int $actingGamePlayerId,
        array $resources,
        ActiveGameState $state,
    ): ActiveGameState {
        return new ActiveGameState(
            players: [
                new ActiveGamePlayer(1, 'Pam', true, 'connected', 1, 0, new PlayerResourceSet(1, 0, 1, 0, 1, 0)),
                new ActiveGamePlayer(2, 'Jim', false, 'connected', 2, 0, new PlayerResourceSet(0, 0, 0, 0, 0, 0)),
            ],
            currentTurnGamePlayerId: 2,
            bank: [
                'coffee' => 3,
                'spreadsheets' => 4,
                'budget' => 3,
                'connections' => 4,
                'time' => 3,
                'executiveFavor' => 5,
            ],
            marketCardsByTier: $state->marketCardsByTier,
            executives: $state->executives,
        );
    }
}
