<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Games;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Games\ActiveGamePlayer;
use Watercooler\Api\Games\ActiveGameState;
use Watercooler\Api\Games\CardSeedDefinition;
use Watercooler\Api\Games\ExecutiveSeedDefinition;
use Watercooler\Api\Games\GameSummary;
use Watercooler\Api\Games\PlayerCardView;
use Watercooler\Api\Games\PlayerResourceSet;
use Watercooler\Api\Games\PurchaseAdvantageException;
use Watercooler\Api\Games\PurchaseAdvantageRepository;
use Watercooler\Api\Games\PurchaseAdvantageService;
use Watercooler\Api\Games\StartGamePlayer;

final class PurchaseAdvantageServiceTest extends TestCase
{
    public function testItPurchasesAMarketCardUsingDiscountsAndExecutiveFavor(): void
    {
        $repository = new InMemoryPurchaseAdvantageRepository();
        $service = new PurchaseAdvantageService($repository);

        $result = $service->purchase('synergy-report-telemetry', [
            'sessionToken' => 'host-token',
            'source' => 'market',
            'tier' => 1,
            'marketSlot' => 1,
        ]);

        $player = $result->state->playerById(1);

        self::assertSame(2, $result->state->currentTurnGamePlayerId);
        self::assertSame(1, $player?->officePrestige);
        self::assertSame(1, $player?->permanentDiscounts['coffee']);
        self::assertSame(0, $player?->resources->coffee);
        self::assertSame(0, $player?->resources->executiveFavor);
        self::assertCount(1, $player?->purchasedCards ?? []);
        self::assertSame('market-card-1', $player?->purchasedCards[0]->code ?? null);
        self::assertSame(4, $result->state->bank['coffee']);
        self::assertSame(5, $result->state->bank['executiveFavor']);
        self::assertSame('replacement-card', $result->state->marketCardsByTier[1][0]->code ?? null);
    }

    public function testItPurchasesAReservedCard(): void
    {
        $repository = new InMemoryPurchaseAdvantageRepository(
            players: [
                new ActiveGamePlayer(
                    1,
                    'Pam',
                    true,
                    'connected',
                    1,
                    0,
                    new PlayerResourceSet(0, 1, 1, 0, 0, 0),
                    reservedCards: [
                        new PlayerCardView('reserved-card-1', 1, 'Reserved Card', 'spreadsheets', 0, [
                            'coffee' => 0,
                            'spreadsheets' => 1,
                            'budget' => 1,
                            'connections' => 0,
                            'time' => 0,
                        ]),
                    ],
                ),
                new ActiveGamePlayer(2, 'Jim', false, 'connected', 2, 0, new PlayerResourceSet(0, 0, 0, 0, 0, 0)),
            ],
        );
        $service = new PurchaseAdvantageService($repository);

        $result = $service->purchase('synergy-report-telemetry', [
            'sessionToken' => 'host-token',
            'source' => 'reserved',
            'cardCode' => 'reserved-card-1',
        ]);

        $player = $result->state->playerById(1);

        self::assertCount(0, $player?->reservedCards ?? []);
        self::assertSame(1, $player?->permanentDiscounts['spreadsheets']);
        self::assertSame('reserved-card-1', $player?->purchasedCards[0]->code ?? null);
    }

    public function testItRejectsUnaffordablePurchases(): void
    {
        $repository = new InMemoryPurchaseAdvantageRepository(
            players: [
                new ActiveGamePlayer(1, 'Pam', true, 'connected', 1, 0, new PlayerResourceSet(0, 0, 0, 0, 0, 0)),
                new ActiveGamePlayer(2, 'Jim', false, 'connected', 2, 0, new PlayerResourceSet(0, 0, 0, 0, 0, 0)),
            ],
        );
        $service = new PurchaseAdvantageService($repository);

        $this->expectException(PurchaseAdvantageException::class);
        $this->expectExceptionMessage('The selected Workplace Advantage is not affordable with the player resources and Executive Favor currently available.');

        $service->purchase('synergy-report-telemetry', [
            'sessionToken' => 'host-token',
            'source' => 'market',
            'tier' => 1,
            'marketSlot' => 1,
        ]);
    }
}

final class InMemoryPurchaseAdvantageRepository implements PurchaseAdvantageRepository
{
    private GameSummary $game;

    /** @var list<ActiveGamePlayer> */
    private array $players;

    /** @var array<string, int> */
    private array $bank;

    /** @var array<int, list<CardSeedDefinition>> */
    private array $marketCardsByTier;

    /** @var list<ExecutiveSeedDefinition> */
    private array $executives;

    /**
     * @param list<ActiveGamePlayer>|null $players
     * @param array<string, int>|null $bank
     * @param array<int, list<CardSeedDefinition>>|null $marketCardsByTier
     */
    public function __construct(?array $players = null, ?array $bank = null, ?array $marketCardsByTier = null)
    {
        $this->game = new GameSummary(1, 'synergy-report-telemetry', 'active', 'active', 2, '2026-04-08 00:00:00');
        $this->players = $players ?? [
            new ActiveGamePlayer(
                1,
                'Pam',
                true,
                'connected',
                1,
                0,
                new PlayerResourceSet(2, 0, 0, 0, 0, 1),
                permanentDiscounts: [
                    'coffee' => 0,
                    'spreadsheets' => 0,
                    'budget' => 0,
                    'connections' => 0,
                    'time' => 0,
                ],
            ),
            new ActiveGamePlayer(2, 'Jim', false, 'connected', 2, 0, new PlayerResourceSet(0, 0, 0, 0, 0, 0)),
        ];
        $this->bank = $bank ?? [
            'coffee' => 2,
            'spreadsheets' => 4,
            'budget' => 4,
            'connections' => 4,
            'time' => 4,
            'executiveFavor' => 4,
        ];
        $this->marketCardsByTier = $marketCardsByTier ?? [
            1 => [
                new CardSeedDefinition('market-card-1', 1, 'Coffee Flow', 'coffee', 1, [
                    'coffee' => 3,
                    'spreadsheets' => 0,
                    'budget' => 0,
                    'connections' => 0,
                    'time' => 0,
                ], 1),
            ],
            2 => [],
            3 => [],
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
            marketCardsByTier: $this->marketCardsByTier,
            executives: $this->executives,
        );
    }

    public function applyPurchaseAdvantage(
        int $gameId,
        int $actingGamePlayerId,
        string $source,
        ?int $tier,
        ?int $marketSlot,
        ?string $cardCode,
        array $spentResources,
        ActiveGameState $state,
    ): ActiveGameState {
        $selectedCard = null;
        $replacement = new CardSeedDefinition('replacement-card', 1, 'Replacement Flow', 'budget', 0, [
            'coffee' => 0,
            'spreadsheets' => 1,
            'budget' => 0,
            'connections' => 1,
            'time' => 1,
        ], 2);

        $updatedMarketCardsByTier = $state->marketCardsByTier;
        $updatedPlayers = [];
        $updatedBank = $state->bank;

        foreach ($spentResources as $resource => $count) {
            $updatedBank[$resource] += $count;
        }

        foreach ($state->players as $player) {
            if ($player->gamePlayerId !== $actingGamePlayerId) {
                $updatedPlayers[] = $player;
                continue;
            }

            if ($source === 'market') {
                $selectedCard = $state->marketCardsByTier[$tier ?? 1][($marketSlot ?? 1) - 1] ?? null;
                $updatedMarketCardsByTier[$tier ?? 1] = [$replacement];
                $updatedReservedCards = $player->reservedCards;
            } else {
                $updatedReservedCards = [];
                foreach ($player->reservedCards as $reservedCard) {
                    if ($reservedCard->code === $cardCode) {
                        $selectedCard = $reservedCard;
                        continue;
                    }

                    $updatedReservedCards[] = $reservedCard;
                }
            }

            if ($selectedCard === null) {
                throw new \RuntimeException('Expected a selected card during purchase test mutation.');
            }

            $updatedPlayers[] = new ActiveGamePlayer(
                gamePlayerId: $player->gamePlayerId,
                displayName: $player->displayName,
                isHost: $player->isHost,
                joinStatus: $player->joinStatus,
                seatOrder: $player->seatOrder,
                officePrestige: $player->officePrestige + $selectedCard->officePrestige,
                resources: new PlayerResourceSet(
                    coffee: $player->resources->coffee - ($spentResources['coffee'] ?? 0),
                    spreadsheets: $player->resources->spreadsheets - ($spentResources['spreadsheets'] ?? 0),
                    budget: $player->resources->budget - ($spentResources['budget'] ?? 0),
                    connections: $player->resources->connections - ($spentResources['connections'] ?? 0),
                    time: $player->resources->time - ($spentResources['time'] ?? 0),
                    executiveFavor: $player->resources->executiveFavor - ($spentResources['executiveFavor'] ?? 0),
                ),
                permanentDiscounts: [
                    ...$player->permanentDiscounts,
                    $selectedCard->resourceDiscount => ($player->permanentDiscounts[$selectedCard->resourceDiscount] ?? 0) + 1,
                ],
                reservedCards: $updatedReservedCards,
                purchasedCards: [...$player->purchasedCards, $selectedCard],
            );
        }

        $this->players = $updatedPlayers;
        $this->bank = $updatedBank;
        $this->marketCardsByTier = $updatedMarketCardsByTier;

        return new ActiveGameState(
            players: $this->players,
            currentTurnGamePlayerId: 2,
            bank: $this->bank,
            marketCardsByTier: $this->marketCardsByTier,
            executives: $state->executives,
        );
    }
}
