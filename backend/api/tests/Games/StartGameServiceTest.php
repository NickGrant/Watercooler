<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Games;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Games\CardSeedDefinition;
use Watercooler\Api\Games\DeckShuffler;
use Watercooler\Api\Games\ExecutiveSeedDefinition;
use Watercooler\Api\Games\GameSummary;
use Watercooler\Api\Games\StartGameException;
use Watercooler\Api\Games\StartGamePlayer;
use Watercooler\Api\Games\StartGameRepository;
use Watercooler\Api\Games\StartGameService;
use Watercooler\Api\Games\StartGameSetup;

final class StartGameServiceTest extends TestCase
{
    public function testItBuildsAndPersistsAStartStateForTheHost(): void
    {
        $repository = new InMemoryStartGameRepository();
        $service = new StartGameService(
            $repository,
            new IdentityDeckShuffler(),
        );

        $result = $service->start('synergy-report-telemetry', [
            'sessionToken' => 'host-token',
        ]);

        self::assertSame('active', $result->game->status);
        self::assertSame('active', $result->game->phase);
        self::assertSame(1, $result->state->currentTurnGamePlayerId);
        self::assertCount(2, $result->state->players);
        self::assertSame(4, $result->state->bank['coffee']);
        self::assertSame(5, $result->state->bank['executiveFavor']);
        self::assertCount(4, $result->state->marketCardsByTier[1]);
        self::assertCount(3, $result->state->executives);
        self::assertNotNull($repository->persistedSetup);
        self::assertSame(1, $repository->persistedSetup?->players[0]->seatOrder);
    }

    public function testItRejectsNonHostStartRequests(): void
    {
        $service = new StartGameService(
            new InMemoryStartGameRepository(),
            new IdentityDeckShuffler(),
        );

        $this->expectException(StartGameException::class);
        $this->expectExceptionMessage('Only the host can start this Watercooler room.');

        $service->start('synergy-report-telemetry', [
            'sessionToken' => 'guest-token',
        ]);
    }

    public function testItRejectsLobbiesWithTooFewPlayers(): void
    {
        $repository = new InMemoryStartGameRepository([
            new StartGamePlayer(1, 'Pam', true, 'connected'),
        ]);
        $service = new StartGameService(
            $repository,
            new IdentityDeckShuffler(),
        );

        $this->expectException(StartGameException::class);
        $this->expectExceptionMessage('At least two employees must join before the game can start.');

        $service->start('synergy-report-telemetry', [
            'sessionToken' => 'host-token',
        ]);
    }
}

final class InMemoryStartGameRepository implements StartGameRepository
{
    private GameSummary $game;

    /** @var list<StartGamePlayer> */
    private array $players;

    /** @var array<string, StartGamePlayer> */
    private array $playersByToken = [];

    public ?StartGameSetup $persistedSetup = null;

    /**
     * @param list<StartGamePlayer>|null $players
     */
    public function __construct(?array $players = null)
    {
        $this->game = new GameSummary(
            id: 1,
            slug: 'synergy-report-telemetry',
            status: 'lobby',
            phase: 'lobby',
            playerCount: $players === null ? 2 : count($players),
            createdAt: '2026-04-08 00:00:00',
        );
        $this->players = $players ?? [
            new StartGamePlayer(1, 'Pam', true, 'connected'),
            new StartGamePlayer(2, 'Jim', false, 'joined'),
        ];
        $this->playersByToken = [
            hash('sha256', 'host-token') => $this->players[0],
            hash('sha256', 'guest-token') => $this->players[count($this->players) > 1 ? 1 : 0],
        ];
    }

    public function findGameBySlug(string $slug): ?GameSummary
    {
        return $slug === $this->game->slug ? $this->game : null;
    }

    public function findPlayerBySessionToken(int $gameId, string $sessionTokenHash): ?StartGamePlayer
    {
        return $this->playersByToken[$sessionTokenHash] ?? null;
    }

    public function listPlayers(int $gameId): array
    {
        return $this->players;
    }

    public function listAvailableCards(): array
    {
        return testCards();
    }

    public function listAvailableExecutives(): array
    {
        return testExecutives();
    }

    public function persistStartedGame(int $gameId, StartGameSetup $setup): GameSummary
    {
        $this->persistedSetup = $setup;
        $this->game = new GameSummary(
            id: $this->game->id,
            slug: $this->game->slug,
            status: 'active',
            phase: 'active',
            playerCount: count($this->players),
            createdAt: $this->game->createdAt,
        );

        return $this->game;
    }
}

final class IdentityDeckShuffler implements DeckShuffler
{
    public function shuffle(array $items): array
    {
        return array_values($items);
    }
}

/**
 * @return list<CardSeedDefinition>
 */
function testCards(): array
{
    return [
        new CardSeedDefinition('t1-01', 1, 'Coffee Workflow 01', 'coffee', 0, ['coffee' => 0, 'spreadsheets' => 1, 'budget' => 1, 'connections' => 1, 'time' => 1], 1),
        new CardSeedDefinition('t1-02', 1, 'Coffee Workflow 02', 'coffee', 0, ['coffee' => 0, 'spreadsheets' => 2, 'budget' => 1, 'connections' => 1, 'time' => 1], 2),
        new CardSeedDefinition('t1-03', 1, 'Coffee Workflow 03', 'coffee', 0, ['coffee' => 0, 'spreadsheets' => 2, 'budget' => 0, 'connections' => 1, 'time' => 2], 3),
        new CardSeedDefinition('t1-04', 1, 'Coffee Workflow 04', 'coffee', 0, ['coffee' => 1, 'spreadsheets' => 0, 'budget' => 1, 'connections' => 3, 'time' => 0], 4),
        new CardSeedDefinition('t1-05', 1, 'Coffee Workflow 05', 'coffee', 0, ['coffee' => 0, 'spreadsheets' => 0, 'budget' => 2, 'connections' => 1, 'time' => 0], 5),
        new CardSeedDefinition('t2-01', 2, 'Budget Workflow 01', 'budget', 1, ['coffee' => 0, 'spreadsheets' => 2, 'budget' => 2, 'connections' => 0, 'time' => 3], 6),
        new CardSeedDefinition('t2-02', 2, 'Budget Workflow 02', 'budget', 1, ['coffee' => 2, 'spreadsheets' => 3, 'budget' => 0, 'connections' => 0, 'time' => 2], 7),
        new CardSeedDefinition('t2-03', 2, 'Budget Workflow 03', 'budget', 2, ['coffee' => 1, 'spreadsheets' => 2, 'budget' => 0, 'connections' => 0, 'time' => 4], 8),
        new CardSeedDefinition('t2-04', 2, 'Budget Workflow 04', 'budget', 2, ['coffee' => 0, 'spreadsheets' => 5, 'budget' => 3, 'connections' => 0, 'time' => 0], 9),
        new CardSeedDefinition('t2-05', 2, 'Budget Workflow 05', 'budget', 2, ['coffee' => 0, 'spreadsheets' => 0, 'budget' => 5, 'connections' => 0, 'time' => 0], 10),
        new CardSeedDefinition('t3-01', 3, 'Executive Coffee Track 01', 'connections', 3, ['coffee' => 0, 'spreadsheets' => 3, 'budget' => 5, 'connections' => 3, 'time' => 3], 11),
        new CardSeedDefinition('t3-02', 3, 'Executive Coffee Track 02', 'connections', 4, ['coffee' => 0, 'spreadsheets' => 0, 'budget' => 0, 'connections' => 7, 'time' => 0], 12),
        new CardSeedDefinition('t3-03', 3, 'Executive Coffee Track 03', 'connections', 4, ['coffee' => 3, 'spreadsheets' => 0, 'budget' => 3, 'connections' => 6, 'time' => 0], 13),
        new CardSeedDefinition('t3-04', 3, 'Executive Coffee Track 04', 'connections', 5, ['coffee' => 3, 'spreadsheets' => 0, 'budget' => 0, 'connections' => 7, 'time' => 0], 14),
        new CardSeedDefinition('t3-05', 3, 'Executive Coffee Track 05', 'connections', 5, ['coffee' => 0, 'spreadsheets' => 3, 'budget' => 0, 'connections' => 0, 'time' => 7], 15),
    ];
}

/**
 * @return list<ExecutiveSeedDefinition>
 */
function testExecutives(): array
{
    return [
        new ExecutiveSeedDefinition('vp-of-synergy', 'VP of Synergy', 3, ['coffee' => 3, 'spreadsheets' => 0, 'budget' => 3, 'connections' => 3, 'time' => 0]),
        new ExecutiveSeedDefinition('chief-efficiency-officer', 'Chief Efficiency Officer', 3, ['coffee' => 0, 'spreadsheets' => 3, 'budget' => 3, 'connections' => 3, 'time' => 0]),
        new ExecutiveSeedDefinition('founders-nephew', "Founder's Nephew", 3, ['coffee' => 0, 'spreadsheets' => 3, 'budget' => 0, 'connections' => 3, 'time' => 3]),
        new ExecutiveSeedDefinition('calendar-strategist', 'Senior Calendar Strategist', 3, ['coffee' => 0, 'spreadsheets' => 4, 'budget' => 0, 'connections' => 0, 'time' => 4]),
    ];
}
