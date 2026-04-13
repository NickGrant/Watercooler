<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Http\Handlers;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Games\ActiveGamePlayer;
use Watercooler\Api\Games\ActiveGameState;
use Watercooler\Api\Games\CardSeedDefinition;
use Watercooler\Api\Games\GameStateProjectionRepository;
use Watercooler\Api\Games\GameSummary;
use Watercooler\Api\Games\LoadGameStateService;
use Watercooler\Api\Games\PlayerResourceSet;
use Watercooler\Api\Http\Handlers\GameStateAction;
use Watercooler\Api\Http\Request;
use Watercooler\Api\Http\Routing\RouteMatch;
use Watercooler\Api\Players\AvatarSelection;
use Watercooler\Api\Players\JoinBootstrapRepository;
use Watercooler\Api\Players\JoinedPlayer;

final class GameStateActionTest extends TestCase
{
    public function testItReturnsLobbyState(): void
    {
        $action = new GameStateAction(
            new LoadGameStateService(
                new HandlerLoadGameStateRepository('lobby'),
                new HandlerGameStateProjectionRepository(),
            ),
        );

        $response = $action(
            new Request('GET', '/api/games/synergy-report-telemetry/state', [], ['sessionToken' => 'host-token'], null),
            new RouteMatch(static fn() => null, ['slug' => 'synergy-report-telemetry']),
        );

        self::assertSame(200, $response->statusCode);
        self::assertStringContainsString('"lobby"', $response->body);
    }

    public function testItReturnsUnauthorizedWithoutASessionToken(): void
    {
        $action = new GameStateAction(
            new LoadGameStateService(
                new HandlerLoadGameStateRepository('active'),
                new HandlerGameStateProjectionRepository(),
            ),
        );

        $response = $action(
            new Request('GET', '/api/games/synergy-report-telemetry/state', [], [], null),
            new RouteMatch(static fn() => null, ['slug' => 'synergy-report-telemetry']),
        );

        self::assertSame(401, $response->statusCode);
        self::assertStringContainsString('session_token_required', $response->body);
    }
}

final class HandlerLoadGameStateRepository implements JoinBootstrapRepository
{
    private GameSummary $game;

    /** @var list<JoinedPlayer> */
    private array $players;

    public function __construct(string $phase)
    {
        $status = $phase === 'lobby' ? 'lobby' : 'active';
        $this->game = new GameSummary(1, 'synergy-report-telemetry', $status, $phase, 2, '2026-04-08 00:00:00');
        $this->players = [
            new JoinedPlayer(1, 1, 'Pam', true, 'connected', new AvatarSelection(id: 'avatar-1')),
        ];
    }

    public function findGameBySlug(string $slug): ?GameSummary
    {
        return $slug === 'synergy-report-telemetry' ? $this->game : null;
    }

    public function displayNameExists(int $gameId, string $displayName): bool
    {
        return false;
    }

    public function findPlayerBySessionToken(int $gameId, string $sessionTokenHash): ?JoinedPlayer
    {
        return $sessionTokenHash === hash('sha256', 'host-token') ? $this->players[0] : null;
    }

    public function createJoinedPlayer(int $gameId, string $displayName, AvatarSelection $avatar, string $sessionTokenHash): JoinedPlayer
    {
        throw new \BadMethodCallException('Not needed in this test.');
    }

    public function listPlayers(int $gameId): array
    {
        return $this->players;
    }
}

final class HandlerGameStateProjectionRepository implements GameStateProjectionRepository
{
    public function loadState(int $gameId): ActiveGameState
    {
        return new ActiveGameState(
            players: [
                new ActiveGamePlayer(1, 'Pam', true, 'connected', 1, 0, new PlayerResourceSet(0, 0, 0, 0, 0, 0)),
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
                1 => [new CardSeedDefinition('t1-01', 1, 'Coffee Workflow 01', 'coffee', 0, ['coffee' => 0, 'spreadsheets' => 1, 'budget' => 1, 'connections' => 1, 'time' => 1], 1)],
                2 => [],
                3 => [],
            ],
            executives: [],
        );
    }
}
