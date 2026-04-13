<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Games;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Games\ActiveGamePlayer;
use Watercooler\Api\Games\ActiveGameState;
use Watercooler\Api\Games\CardSeedDefinition;
use Watercooler\Api\Games\GameStateProjectionRepository;
use Watercooler\Api\Games\GameSummary;
use Watercooler\Api\Games\LoadGameStateException;
use Watercooler\Api\Games\LoadGameStateService;
use Watercooler\Api\Games\PlayerResourceSet;
use Watercooler\Api\Players\AvatarSelection;
use Watercooler\Api\Players\JoinBootstrapRepository;
use Watercooler\Api\Players\JoinedPlayer;

final class LoadGameStateServiceTest extends TestCase
{
    public function testItReturnsLobbyStateForALobbyGame(): void
    {
        $service = new LoadGameStateService(
            new InMemoryLoadGameStateRepository('lobby'),
            new InMemoryGameStateProjectionRepository(),
        );

        $result = $service->load('synergy-report-telemetry', 'host-token');

        self::assertArrayHasKey('lobby', $result->toArray());
        self::assertArrayNotHasKey('state', $result->toArray());
        self::assertSame('lobby', $result->game->phase);
    }

    public function testItReturnsActiveStateForAnActiveGame(): void
    {
        $service = new LoadGameStateService(
            new InMemoryLoadGameStateRepository('active'),
            new InMemoryGameStateProjectionRepository(),
        );

        $result = $service->load('synergy-report-telemetry', 'host-token');

        self::assertArrayHasKey('state', $result->toArray());
        self::assertSame(1, $result->state?->currentTurnGamePlayerId);
    }

    public function testItRequiresASessionToken(): void
    {
        $service = new LoadGameStateService(
            new InMemoryLoadGameStateRepository('active'),
            new InMemoryGameStateProjectionRepository(),
        );

        $this->expectException(LoadGameStateException::class);
        $this->expectExceptionMessage('A valid temporary session token is required to load game state.');

        $service->load('synergy-report-telemetry', '');
    }
}

final class InMemoryLoadGameStateRepository implements JoinBootstrapRepository
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
            new JoinedPlayer(2, 2, 'Jim', false, 'connected', new AvatarSelection(id: 'avatar-2')),
        ];
    }

    public function findGameBySlug(string $slug): ?GameSummary
    {
        return $slug === $this->game->slug ? $this->game : null;
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

final class InMemoryGameStateProjectionRepository implements GameStateProjectionRepository
{
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
                1 => [new CardSeedDefinition('t1-01', 1, 'Coffee Workflow 01', 'coffee', 0, ['coffee' => 0, 'spreadsheets' => 1, 'budget' => 1, 'connections' => 1, 'time' => 1], 1)],
                2 => [],
                3 => [],
            ],
            executives: [],
        );
    }
}
