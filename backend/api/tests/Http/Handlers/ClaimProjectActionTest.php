<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Http\Handlers;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Games\ActiveGamePlayer;
use Watercooler\Api\Games\ActiveGameState;
use Watercooler\Api\Games\CardSeedDefinition;
use Watercooler\Api\Games\ClaimProjectRepository;
use Watercooler\Api\Games\ClaimProjectService;
use Watercooler\Api\Games\ExecutiveSeedDefinition;
use Watercooler\Api\Games\GameSummary;
use Watercooler\Api\Games\PlayerCardView;
use Watercooler\Api\Games\PlayerResourceSet;
use Watercooler\Api\Games\StartGamePlayer;
use Watercooler\Api\Http\Handlers\ClaimProjectAction;
use Watercooler\Api\Http\Request;
use Watercooler\Api\Http\Routing\RouteMatch;

final class ClaimProjectActionTest extends TestCase
{
    public function testItReturnsTheUpdatedReservedState(): void
    {
        $action = new ClaimProjectAction(
            new ClaimProjectService(new HandlerClaimProjectRepository()),
        );

        $response = $action(
            new Request(
                'POST',
                '/api/games/synergy-report-telemetry/claim-project',
                [],
                [],
                [
                    'sessionToken' => 'host-token',
                    'source' => 'market',
                    'tier' => 1,
                    'marketSlot' => 1,
                ],
            ),
            new RouteMatch(static fn() => null, ['slug' => 'synergy-report-telemetry']),
        );

        self::assertSame(200, $response->statusCode);
        self::assertStringContainsString('"reservedCards"', $response->body);
        self::assertStringContainsString('"executiveFavor": 1', $response->body);
    }

    public function testItReturnsValidationErrorsAsJson(): void
    {
        $action = new ClaimProjectAction(
            new ClaimProjectService(new HandlerClaimProjectRepository()),
        );

        $response = $action(
            new Request(
                'POST',
                '/api/games/synergy-report-telemetry/claim-project',
                [],
                [],
                [
                    'sessionToken' => 'host-token',
                    'source' => 'unknown',
                    'tier' => 1,
                ],
            ),
            new RouteMatch(static fn() => null, ['slug' => 'synergy-report-telemetry']),
        );

        self::assertSame(422, $response->statusCode);
        self::assertStringContainsString('invalid_claim_source', $response->body);
    }

    public function testItReturnsRecoverableConflictStateWhenTheActionIsStale(): void
    {
        $action = new ClaimProjectAction(
            new ClaimProjectService(new HandlerClaimProjectRepository()),
        );

        $response = $action(
            new Request(
                'POST',
                '/api/games/synergy-report-telemetry/claim-project',
                [],
                [],
                [
                    'sessionToken' => 'guest-token',
                    'source' => 'market',
                    'tier' => 1,
                    'marketSlot' => 1,
                ],
            ),
            new RouteMatch(static fn() => null, ['slug' => 'synergy-report-telemetry']),
        );

        self::assertSame(409, $response->statusCode);
        self::assertStringContainsString('"shouldResync": true', $response->body);
        self::assertStringContainsString('"currentTurnGamePlayerId": 1', $response->body);
    }
}

final class HandlerClaimProjectRepository implements ClaimProjectRepository
{
    public function findGameBySlug(string $slug): ?GameSummary
    {
        return $slug === 'synergy-report-telemetry'
            ? new GameSummary(1, 'synergy-report-telemetry', 'active', 'active', 2, '2026-04-08 00:00:00')
            : null;
    }

    public function findPlayerBySessionToken(int $gameId, string $sessionTokenHash): ?StartGamePlayer
    {
        return match ($sessionTokenHash) {
            hash('sha256', 'host-token') => new StartGamePlayer(1, 'Pam', true, 'connected', 1, 0),
            hash('sha256', 'guest-token') => new StartGamePlayer(2, 'Jim', false, 'connected', 2, 0),
            default => null,
        };
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
                    new CardSeedDefinition('m1', 1, 'Market Card', 'coffee', 0, ['coffee' => 0, 'spreadsheets' => 1, 'budget' => 1, 'connections' => 1, 'time' => 1], 1),
                ],
                2 => [],
                3 => [],
            ],
            executives: [
                new ExecutiveSeedDefinition('vp-of-synergy', 'VP of Synergy', 3, ['coffee' => 3, 'spreadsheets' => 0, 'budget' => 3, 'connections' => 3, 'time' => 0]),
            ],
        );
    }

    public function applyClaimProject(
        int $gameId,
        int $actingGamePlayerId,
        string $source,
        int $tier,
        ?int $marketSlot,
        ActiveGameState $state,
    ): ActiveGameState {
        return new ActiveGameState(
            players: [
                new ActiveGamePlayer(
                    1,
                    'Pam',
                    true,
                    'connected',
                    1,
                    0,
                    new PlayerResourceSet(0, 0, 0, 0, 0, 1),
                    reservedCards: [
                        new PlayerCardView('reserved-1', 1, 'Reserved Card', 'coffee', 0, ['coffee' => 0, 'spreadsheets' => 1, 'budget' => 1, 'connections' => 1, 'time' => 1]),
                    ],
                ),
                new ActiveGamePlayer(2, 'Jim', false, 'connected', 2, 0, new PlayerResourceSet(0, 0, 0, 0, 0, 0)),
            ],
            currentTurnGamePlayerId: 2,
            bank: [
                'coffee' => 4,
                'spreadsheets' => 4,
                'budget' => 4,
                'connections' => 4,
                'time' => 4,
                'executiveFavor' => 4,
            ],
            marketCardsByTier: $state->marketCardsByTier,
            executives: $state->executives,
        );
    }
}
