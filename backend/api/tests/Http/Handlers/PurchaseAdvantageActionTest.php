<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Http\Handlers;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Games\ActiveGamePlayer;
use Watercooler\Api\Games\ActiveGameState;
use Watercooler\Api\Games\CardSeedDefinition;
use Watercooler\Api\Games\ExecutiveSeedDefinition;
use Watercooler\Api\Games\GameSummary;
use Watercooler\Api\Games\PlayerCardView;
use Watercooler\Api\Games\PlayerExecutiveView;
use Watercooler\Api\Games\PlayerResourceSet;
use Watercooler\Api\Games\PurchaseAdvantageRepository;
use Watercooler\Api\Games\PurchaseAdvantageService;
use Watercooler\Api\Games\StartGamePlayer;
use Watercooler\Api\Http\Handlers\PurchaseAdvantageAction;
use Watercooler\Api\Http\Request;
use Watercooler\Api\Http\Routing\RouteMatch;

final class PurchaseAdvantageActionTest extends TestCase
{
    public function testItReturnsTheUpdatedPurchasedState(): void
    {
        $action = new PurchaseAdvantageAction(
            new PurchaseAdvantageService(new HandlerPurchaseAdvantageRepository()),
        );

        $response = $action(
            new Request(
                'POST',
                '/api/games/synergy-report-telemetry/purchase-advantage',
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
        self::assertStringContainsString('"purchasedCards"', $response->body);
        self::assertStringContainsString('"purchasedCardCount": 1', $response->body);
        self::assertStringContainsString('"executiveFavor": 0', $response->body);
        self::assertStringContainsString('"claimedExecutives"', $response->body);
    }

    public function testItReturnsValidationErrorsAsJson(): void
    {
        $action = new PurchaseAdvantageAction(
            new PurchaseAdvantageService(new HandlerPurchaseAdvantageRepository()),
        );

        $response = $action(
            new Request(
                'POST',
                '/api/games/synergy-report-telemetry/purchase-advantage',
                [],
                [],
                [
                    'sessionToken' => 'host-token',
                    'source' => 'unknown',
                ],
            ),
            new RouteMatch(static fn() => null, ['slug' => 'synergy-report-telemetry']),
        );

        self::assertSame(422, $response->statusCode);
        self::assertStringContainsString('invalid_purchase_source', $response->body);
    }
}

final class HandlerPurchaseAdvantageRepository implements PurchaseAdvantageRepository
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
                new ActiveGamePlayer(1, 'Pam', true, 'connected', 1, 0, new PlayerResourceSet(2, 0, 0, 0, 0, 1)),
                new ActiveGamePlayer(2, 'Jim', false, 'connected', 2, 0, new PlayerResourceSet(0, 0, 0, 0, 0, 0)),
            ],
            currentTurnGamePlayerId: 1,
            bank: [
                'coffee' => 2,
                'spreadsheets' => 4,
                'budget' => 4,
                'connections' => 4,
                'time' => 4,
                'executiveFavor' => 4,
            ],
            marketCardsByTier: [
                1 => [
                    new CardSeedDefinition('market-card-1', 1, 'Coffee Flow', 'coffee', 1, ['coffee' => 3, 'spreadsheets' => 0, 'budget' => 0, 'connections' => 0, 'time' => 0], 1),
                ],
                2 => [],
                3 => [],
            ],
            executives: [
                new ExecutiveSeedDefinition('vp-of-synergy', 'VP of Synergy', 3, ['coffee' => 3, 'spreadsheets' => 0, 'budget' => 3, 'connections' => 3, 'time' => 0]),
            ],
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
        return new ActiveGameState(
            players: [
                new ActiveGamePlayer(
                    1,
                    'Pam',
                    true,
                    'connected',
                    1,
                    1,
                    new PlayerResourceSet(1, 0, 0, 0, 0, 0),
                    permanentDiscounts: [
                        'coffee' => 1,
                        'spreadsheets' => 0,
                        'budget' => 0,
                        'connections' => 0,
                        'time' => 0,
                    ],
                    reservedCards: [],
                    purchasedCards: [
                        new PlayerCardView('market-card-1', 1, 'Coffee Flow', 'coffee', 1, ['coffee' => 3, 'spreadsheets' => 0, 'budget' => 0, 'connections' => 0, 'time' => 0]),
                    ],
                    claimedExecutives: [
                        new PlayerExecutiveView('vp-of-synergy', 'VP of Synergy', 3, ['coffee' => 3, 'spreadsheets' => 0, 'budget' => 3, 'connections' => 3, 'time' => 0]),
                    ],
                ),
                new ActiveGamePlayer(2, 'Jim', false, 'connected', 2, 0, new PlayerResourceSet(0, 0, 0, 0, 0, 0)),
            ],
            currentTurnGamePlayerId: 2,
            bank: [
                'coffee' => 3,
                'spreadsheets' => 4,
                'budget' => 4,
                'connections' => 4,
                'time' => 4,
                'executiveFavor' => 5,
            ],
            marketCardsByTier: $state->marketCardsByTier,
            executives: [],
        );
    }
}
