<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Games;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Games\ActiveGamePlayer;
use Watercooler\Api\Games\ActiveGameState;
use Watercooler\Api\Games\CardSeedDefinition;
use Watercooler\Api\Games\ExecutiveSeedDefinition;
use Watercooler\Api\Games\GameSummary;
use Watercooler\Api\Games\PlayerResourceSet;
use Watercooler\Api\Games\PlayerCardView;
use Watercooler\Api\Games\StartGamePlayer;
use Watercooler\Api\Games\TakeResourcesException;
use Watercooler\Api\Games\TakeResourcesRepository;
use Watercooler\Api\Games\TakeResourcesService;

final class TakeResourcesServiceTest extends TestCase
{
    public function testItAllowsThreeDistinctResourcesOnThePlayersTurn(): void
    {
        $repository = new InMemoryTakeResourcesRepository();
        $service = new TakeResourcesService($repository);

        $result = $service->take('synergy-report-telemetry', [
            'sessionToken' => 'host-token',
            'resources' => ['coffee', 'budget', 'time'],
        ]);

        self::assertSame(2, $result->state->currentTurnGamePlayerId);
        self::assertSame(3, $result->state->playerById(1)?->resources->totalTokens());
        self::assertSame(3, $result->state->bank['coffee']);
        self::assertSame(3, $result->state->bank['budget']);
        self::assertSame(3, $result->state->bank['time']);
    }

    public function testItAllowsTwoMatchingResourcesWhenTheBankHasAtLeastFour(): void
    {
        $repository = new InMemoryTakeResourcesRepository();
        $service = new TakeResourcesService($repository);

        $result = $service->take('synergy-report-telemetry', [
            'sessionToken' => 'host-token',
            'resources' => ['coffee', 'coffee'],
        ]);

        self::assertSame(2, $result->state->playerById(1)?->resources->coffee);
        self::assertSame(2, $result->state->bank['coffee']);
    }

    public function testItRejectsActionsTakenOutOfTurn(): void
    {
        $service = new TakeResourcesService(new InMemoryTakeResourcesRepository());

        try {
            $service->take('synergy-report-telemetry', [
                'sessionToken' => 'guest-token',
                'resources' => ['coffee', 'budget', 'time'],
            ]);
            self::fail('Expected a TakeResourcesException to be thrown.');
        } catch (TakeResourcesException $exception) {
            self::assertSame('Only the active player may take resources right now.', $exception->getMessage());
            self::assertTrue($exception->recovery['shouldResync'] ?? false);
            self::assertSame(1, $exception->recovery['state']['currentTurnGamePlayerId'] ?? null);
        }
    }

    public function testItRejectsDoubleTakesWhenTheBankIsTooLow(): void
    {
        $repository = new InMemoryTakeResourcesRepository(
            bank: [
                'coffee' => 3,
                'spreadsheets' => 4,
                'budget' => 4,
                'connections' => 4,
                'time' => 4,
                'executiveFavor' => 5,
            ],
        );
        $service = new TakeResourcesService($repository);

        $this->expectException(TakeResourcesException::class);
        $this->expectExceptionMessage('Taking two matching resources requires at least four of that resource in the bank beforehand.');

        $service->take('synergy-report-telemetry', [
            'sessionToken' => 'host-token',
            'resources' => ['coffee', 'coffee'],
        ]);
    }

    public function testItRejectsSelectionsThatWouldPushThePlayerAboveTenTokens(): void
    {
        $repository = new InMemoryTakeResourcesRepository(
            players: [
                new ActiveGamePlayer(1, 'Pam', true, 'connected', 1, 0, new PlayerResourceSet(4, 2, 2, 0, 0, 0)),
                new ActiveGamePlayer(2, 'Jim', false, 'connected', 2, 0, new PlayerResourceSet(0, 0, 0, 0, 0, 0)),
            ],
        );
        $service = new TakeResourcesService($repository);

        $this->expectException(TakeResourcesException::class);
        $this->expectExceptionMessage('A player may not hold more than ten resources after taking from the bank.');

        $service->take('synergy-report-telemetry', [
            'sessionToken' => 'host-token',
            'resources' => ['coffee', 'budget', 'time'],
        ]);
    }

    public function testItRejectsThreeResourceSelectionsThatAreNotDistinct(): void
    {
        $service = new TakeResourcesService(new InMemoryTakeResourcesRepository());

        $this->expectException(TakeResourcesException::class);
        $this->expectExceptionMessage('Taking three resources requires three different resource colors.');

        $service->take('synergy-report-telemetry', [
            'sessionToken' => 'host-token',
            'resources' => ['coffee', 'coffee', 'budget'],
        ]);
    }

    public function testItAllowsResourceTakingDuringTheEndgameRound(): void
    {
        $repository = new InMemoryTakeResourcesRepository(gamePhase: 'endgame');
        $service = new TakeResourcesService($repository);

        $result = $service->take('synergy-report-telemetry', [
            'sessionToken' => 'host-token',
            'resources' => ['coffee', 'budget', 'time'],
        ]);

        self::assertSame(2, $result->state->currentTurnGamePlayerId);
        self::assertSame('endgame', $result->game->phase);
    }
}

final class InMemoryTakeResourcesRepository implements TakeResourcesRepository
{
    private GameSummary $game;

    /** @var list<ActiveGamePlayer> */
    private array $players;

    /** @var array<string, int> */
    private array $bank;

    /** @var list<CardSeedDefinition> */
    private array $tierOneCards;

    /** @var list<ExecutiveSeedDefinition> */
    private array $executives;

    /**
     * @param list<ActiveGamePlayer>|null $players
     * @param array<string, int>|null $bank
     */
    public function __construct(?array $players = null, ?array $bank = null, string $gamePhase = 'active')
    {
        $this->game = new GameSummary(1, 'synergy-report-telemetry', 'active', $gamePhase, 2, '2026-04-08 00:00:00');
        $this->players = $players ?? [
            new ActiveGamePlayer(1, 'Pam', true, 'connected', 1, 0, new PlayerResourceSet(0, 0, 0, 0, 0, 0)),
            new ActiveGamePlayer(2, 'Jim', false, 'connected', 2, 0, new PlayerResourceSet(0, 0, 0, 0, 0, 0)),
        ];
        $this->bank = $bank ?? [
            'coffee' => 4,
            'spreadsheets' => 4,
            'budget' => 4,
            'connections' => 4,
            'time' => 4,
            'executiveFavor' => 5,
        ];
        $this->tierOneCards = [
            new CardSeedDefinition('t1-01', 1, 'Coffee Workflow 01', 'coffee', 0, ['coffee' => 0, 'spreadsheets' => 1, 'budget' => 1, 'connections' => 1, 'time' => 1], 1),
            new CardSeedDefinition('t1-02', 1, 'Spreadsheet Workflow 01', 'spreadsheets', 0, ['coffee' => 1, 'spreadsheets' => 0, 'budget' => 1, 'connections' => 1, 'time' => 1], 2),
            new CardSeedDefinition('t1-03', 1, 'Budget Workflow 01', 'budget', 0, ['coffee' => 1, 'spreadsheets' => 1, 'budget' => 0, 'connections' => 1, 'time' => 1], 3),
            new CardSeedDefinition('t1-04', 1, 'Time Workflow 01', 'time', 0, ['coffee' => 1, 'spreadsheets' => 1, 'budget' => 1, 'connections' => 1, 'time' => 0], 4),
        ];
        $this->executives = [
            new ExecutiveSeedDefinition('vp-of-synergy', 'VP of Synergy', 3, ['coffee' => 3, 'spreadsheets' => 0, 'budget' => 3, 'connections' => 3, 'time' => 0]),
        ];
    }

    public function findGameBySlug(string $slug): ?GameSummary
    {
        return $slug === $this->game->slug ? $this->game : null;
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
            players: $this->players,
            currentTurnGamePlayerId: 1,
            bank: $this->bank,
            marketCardsByTier: [
                1 => $this->tierOneCards,
                2 => [],
                3 => [],
            ],
            executives: $this->executives,
        );
    }

    public function applyTakeResources(
        int $gameId,
        int $actingGamePlayerId,
        array $resources,
        ActiveGameState $state,
    ): ActiveGameState {
        $resourceCounts = array_count_values($resources);
        $updatedPlayers = [];

        foreach ($state->players as $player) {
            if ($player->gamePlayerId !== $actingGamePlayerId) {
                $updatedPlayers[] = $player;
                continue;
            }

            $updatedPlayers[] = new ActiveGamePlayer(
                gamePlayerId: $player->gamePlayerId,
                displayName: $player->displayName,
                isHost: $player->isHost,
                joinStatus: $player->joinStatus,
                seatOrder: $player->seatOrder,
                officePrestige: $player->officePrestige,
                resources: new PlayerResourceSet(
                    coffee: $player->resources->coffee + ($resourceCounts['coffee'] ?? 0),
                    spreadsheets: $player->resources->spreadsheets + ($resourceCounts['spreadsheets'] ?? 0),
                    budget: $player->resources->budget + ($resourceCounts['budget'] ?? 0),
                    connections: $player->resources->connections + ($resourceCounts['connections'] ?? 0),
                    time: $player->resources->time + ($resourceCounts['time'] ?? 0),
                    executiveFavor: $player->resources->executiveFavor,
                ),
                permanentDiscounts: $player->permanentDiscounts,
                reservedCards: $player->reservedCards,
                purchasedCards: $player->purchasedCards,
            );
        }

        $updatedBank = $state->bank;
        foreach ($resourceCounts as $resource => $count) {
            $updatedBank[$resource] -= $count;
        }

        return new ActiveGameState(
            players: $updatedPlayers,
            currentTurnGamePlayerId: 2,
            bank: $updatedBank,
            marketCardsByTier: $state->marketCardsByTier,
            executives: $state->executives,
        );
    }
}
