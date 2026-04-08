<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Http\Handlers;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Games\CardSeedDefinition;
use Watercooler\Api\Games\DeckShuffler;
use Watercooler\Api\Games\ExecutiveSeedDefinition;
use Watercooler\Api\Games\GameSummary;
use Watercooler\Api\Games\StartGamePlayer;
use Watercooler\Api\Games\StartGameRepository;
use Watercooler\Api\Games\StartGameService;
use Watercooler\Api\Games\StartGameSetup;
use Watercooler\Api\Http\Handlers\StartGameAction;
use Watercooler\Api\Http\Request;
use Watercooler\Api\Http\Routing\RouteMatch;

final class StartGameActionTest extends TestCase
{
    public function testItReturnsTheStartedGamePayload(): void
    {
        $action = new StartGameAction(
            new StartGameService(
                new HandlerStartGameRepository(),
                new HandlerIdentityShuffler(),
            ),
        );

        $response = $action(
            new Request(
                'POST',
                '/api/games/synergy-report-telemetry/start',
                [],
                [],
                ['sessionToken' => 'host-token'],
            ),
            new RouteMatch(static fn() => null, ['slug' => 'synergy-report-telemetry']),
        );

        self::assertSame(200, $response->statusCode);
        self::assertStringContainsString('"phase": "active"', $response->body);
        self::assertStringContainsString('"executiveFavor": 5', $response->body);
    }

    public function testItReturnsForbiddenWhenANonHostAttemptsToStart(): void
    {
        $action = new StartGameAction(
            new StartGameService(
                new HandlerStartGameRepository(),
                new HandlerIdentityShuffler(),
            ),
        );

        $response = $action(
            new Request(
                'POST',
                '/api/games/synergy-report-telemetry/start',
                [],
                [],
                ['sessionToken' => 'guest-token'],
            ),
            new RouteMatch(static fn() => null, ['slug' => 'synergy-report-telemetry']),
        );

        self::assertSame(403, $response->statusCode);
        self::assertStringContainsString('host_required', $response->body);
    }
}

final class HandlerStartGameRepository implements StartGameRepository
{
    private GameSummary $game;

    /** @var list<StartGamePlayer> */
    private array $players;

    /** @var array<string, StartGamePlayer> */
    private array $playerByToken;

    public function __construct()
    {
        $this->game = new GameSummary(1, 'synergy-report-telemetry', 'lobby', 'lobby', 2, '2026-04-08 00:00:00');
        $this->players = [
            new StartGamePlayer(1, 'Pam', true, 'connected'),
            new StartGamePlayer(2, 'Jim', false, 'joined'),
        ];
        $this->playerByToken = [
            hash('sha256', 'host-token') => $this->players[0],
            hash('sha256', 'guest-token') => $this->players[1],
        ];
    }

    public function findGameBySlug(string $slug): ?GameSummary
    {
        return $slug === $this->game->slug ? $this->game : null;
    }

    public function findPlayerBySessionToken(int $gameId, string $sessionTokenHash): ?StartGamePlayer
    {
        return $this->playerByToken[$sessionTokenHash] ?? null;
    }

    public function listPlayers(int $gameId): array
    {
        return $this->players;
    }

    public function listAvailableCards(): array
    {
        return [
            new CardSeedDefinition('t1-01', 1, 'Coffee Workflow 01', 'coffee', 0, ['coffee' => 0, 'spreadsheets' => 1, 'budget' => 1, 'connections' => 1, 'time' => 1], 1),
            new CardSeedDefinition('t1-02', 1, 'Coffee Workflow 02', 'coffee', 0, ['coffee' => 0, 'spreadsheets' => 2, 'budget' => 1, 'connections' => 1, 'time' => 1], 2),
            new CardSeedDefinition('t1-03', 1, 'Coffee Workflow 03', 'coffee', 0, ['coffee' => 0, 'spreadsheets' => 2, 'budget' => 0, 'connections' => 1, 'time' => 2], 3),
            new CardSeedDefinition('t1-04', 1, 'Coffee Workflow 04', 'coffee', 0, ['coffee' => 1, 'spreadsheets' => 0, 'budget' => 1, 'connections' => 3, 'time' => 0], 4),
            new CardSeedDefinition('t2-01', 2, 'Budget Workflow 01', 'budget', 1, ['coffee' => 0, 'spreadsheets' => 2, 'budget' => 2, 'connections' => 0, 'time' => 3], 5),
            new CardSeedDefinition('t2-02', 2, 'Budget Workflow 02', 'budget', 1, ['coffee' => 2, 'spreadsheets' => 3, 'budget' => 0, 'connections' => 0, 'time' => 2], 6),
            new CardSeedDefinition('t2-03', 2, 'Budget Workflow 03', 'budget', 2, ['coffee' => 1, 'spreadsheets' => 2, 'budget' => 0, 'connections' => 0, 'time' => 4], 7),
            new CardSeedDefinition('t2-04', 2, 'Budget Workflow 04', 'budget', 2, ['coffee' => 0, 'spreadsheets' => 5, 'budget' => 3, 'connections' => 0, 'time' => 0], 8),
            new CardSeedDefinition('t3-01', 3, 'Executive Coffee Track 01', 'connections', 3, ['coffee' => 0, 'spreadsheets' => 3, 'budget' => 5, 'connections' => 3, 'time' => 3], 9),
            new CardSeedDefinition('t3-02', 3, 'Executive Coffee Track 02', 'connections', 4, ['coffee' => 0, 'spreadsheets' => 0, 'budget' => 0, 'connections' => 7, 'time' => 0], 10),
            new CardSeedDefinition('t3-03', 3, 'Executive Coffee Track 03', 'connections', 4, ['coffee' => 3, 'spreadsheets' => 0, 'budget' => 3, 'connections' => 6, 'time' => 0], 11),
            new CardSeedDefinition('t3-04', 3, 'Executive Coffee Track 04', 'connections', 5, ['coffee' => 3, 'spreadsheets' => 0, 'budget' => 0, 'connections' => 7, 'time' => 0], 12),
        ];
    }

    public function listAvailableExecutives(): array
    {
        return [
            new ExecutiveSeedDefinition('vp-of-synergy', 'VP of Synergy', 3, ['coffee' => 3, 'spreadsheets' => 0, 'budget' => 3, 'connections' => 3, 'time' => 0]),
            new ExecutiveSeedDefinition('chief-efficiency-officer', 'Chief Efficiency Officer', 3, ['coffee' => 0, 'spreadsheets' => 3, 'budget' => 3, 'connections' => 3, 'time' => 0]),
            new ExecutiveSeedDefinition('founders-nephew', "Founder's Nephew", 3, ['coffee' => 0, 'spreadsheets' => 3, 'budget' => 0, 'connections' => 3, 'time' => 3]),
        ];
    }

    public function persistStartedGame(int $gameId, StartGameSetup $setup): GameSummary
    {
        return new GameSummary(1, 'synergy-report-telemetry', 'active', 'active', 2, '2026-04-08 00:00:00');
    }
}

final class HandlerIdentityShuffler implements DeckShuffler
{
    public function shuffle(array $items): array
    {
        return array_values($items);
    }
}
