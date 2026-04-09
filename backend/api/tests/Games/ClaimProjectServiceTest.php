<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Games;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Games\ActiveGamePlayer;
use Watercooler\Api\Games\ActiveGameState;
use Watercooler\Api\Games\CardSeedDefinition;
use Watercooler\Api\Games\ClaimProjectException;
use Watercooler\Api\Games\ClaimProjectRepository;
use Watercooler\Api\Games\ClaimProjectService;
use Watercooler\Api\Games\ExecutiveSeedDefinition;
use Watercooler\Api\Games\GameSummary;
use Watercooler\Api\Games\PlayerCardView;
use Watercooler\Api\Games\PlayerResourceSet;
use Watercooler\Api\Games\StartGamePlayer;

final class ClaimProjectServiceTest extends TestCase
{
    public function testItClaimsAMarketCardAndAwardsExecutiveFavorWhenAvailable(): void
    {
        $repository = new InMemoryClaimProjectRepository();
        $service = new ClaimProjectService($repository);

        $result = $service->claim('synergy-report-telemetry', [
            'sessionToken' => 'host-token',
            'source' => 'market',
            'tier' => 1,
            'marketSlot' => 2,
        ]);

        $player = $result->state->playerById(1);

        self::assertSame(2, $result->state->currentTurnGamePlayerId);
        self::assertCount(1, $player?->reservedCards ?? []);
        self::assertSame(1, $player?->resources->executiveFavor);
        self::assertSame(4, $result->state->bank['executiveFavor']);
    }

    public function testItRejectsMoreThanThreeReservedCards(): void
    {
        $repository = new InMemoryClaimProjectRepository(
            players: [
                new ActiveGamePlayer(
                    1,
                    'Pam',
                    true,
                    'connected',
                    1,
                    0,
                    new PlayerResourceSet(0, 0, 0, 0, 0, 0),
                    reservedCards: [
                        reserveCard('r1'),
                        reserveCard('r2'),
                        reserveCard('r3'),
                    ],
                ),
                new ActiveGamePlayer(2, 'Jim', false, 'connected', 2, 0, new PlayerResourceSet(0, 0, 0, 0, 0, 0)),
            ],
        );
        $service = new ClaimProjectService($repository);

        $this->expectException(ClaimProjectException::class);
        $this->expectExceptionMessage('A player may not hold more than three claimed projects.');

        $service->claim('synergy-report-telemetry', [
            'sessionToken' => 'host-token',
            'source' => 'market',
            'tier' => 1,
            'marketSlot' => 1,
        ]);
    }

    public function testItRejectsClaimingWhenExecutiveFavorWouldBreakTheTokenLimit(): void
    {
        $repository = new InMemoryClaimProjectRepository(
            players: [
                new ActiveGamePlayer(1, 'Pam', true, 'connected', 1, 0, new PlayerResourceSet(2, 2, 2, 2, 2, 0)),
                new ActiveGamePlayer(2, 'Jim', false, 'connected', 2, 0, new PlayerResourceSet(0, 0, 0, 0, 0, 0)),
            ],
        );
        $service = new ClaimProjectService($repository);

        $this->expectException(ClaimProjectException::class);
        $this->expectExceptionMessage('Claiming a project while Executive Favor is available would exceed the 10-resource limit.');

        $service->claim('synergy-report-telemetry', [
            'sessionToken' => 'host-token',
            'source' => 'market',
            'tier' => 1,
            'marketSlot' => 1,
        ]);
    }

    public function testItRejectsInvalidClaimSources(): void
    {
        $service = new ClaimProjectService(new InMemoryClaimProjectRepository());

        $this->expectException(ClaimProjectException::class);
        $this->expectExceptionMessage('Claimed projects must come from either the market or the top of a tier deck.');

        $service->claim('synergy-report-telemetry', [
            'sessionToken' => 'host-token',
            'source' => 'unknown',
            'tier' => 1,
        ]);
    }

    public function testItAllowsClaimingDuringTheEndgameRound(): void
    {
        $repository = new InMemoryClaimProjectRepository(gamePhase: 'endgame');
        $service = new ClaimProjectService($repository);

        $result = $service->claim('synergy-report-telemetry', [
            'sessionToken' => 'host-token',
            'source' => 'market',
            'tier' => 1,
            'marketSlot' => 1,
        ]);

        self::assertSame(2, $result->state->currentTurnGamePlayerId);
        self::assertSame('endgame', $result->game->phase);
    }
}

final class InMemoryClaimProjectRepository implements ClaimProjectRepository
{
    private GameSummary $game;

    /** @var list<ActiveGamePlayer> */
    private array $players;

    /** @var array<string, int> */
    private array $bank;

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
                1 => [marketCard('m1')],
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
                    coffee: $player->resources->coffee,
                    spreadsheets: $player->resources->spreadsheets,
                    budget: $player->resources->budget,
                    connections: $player->resources->connections,
                    time: $player->resources->time,
                    executiveFavor: $player->resources->executiveFavor + (($state->bank['executiveFavor'] ?? 0) > 0 ? 1 : 0),
                ),
                permanentDiscounts: $player->permanentDiscounts,
                reservedCards: [...$player->reservedCards, reserveCard('claimed-market-card')],
                purchasedCards: $player->purchasedCards,
            );
        }

        return new ActiveGameState(
            players: $updatedPlayers,
            currentTurnGamePlayerId: 2,
            bank: [
                ...$state->bank,
                'executiveFavor' => max(0, $state->bank['executiveFavor'] - 1),
            ],
            marketCardsByTier: $state->marketCardsByTier,
            executives: $state->executives,
        );
    }
}

function reserveCard(string $code): PlayerCardView
{
    return new PlayerCardView($code, 1, 'Reserved Project', 'coffee', 0, [
        'coffee' => 0,
        'spreadsheets' => 1,
        'budget' => 1,
        'connections' => 1,
        'time' => 1,
    ]);
}

function marketCard(string $code): CardSeedDefinition
{
    return new CardSeedDefinition($code, 1, 'Market Project', 'coffee', 0, [
        'coffee' => 0,
        'spreadsheets' => 1,
        'budget' => 1,
        'connections' => 1,
        'time' => 1,
    ], 1);
}
