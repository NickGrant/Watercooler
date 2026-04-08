<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Http\Handlers;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Games\GameRepository;
use Watercooler\Api\Games\GameSummary;
use Watercooler\Api\Http\Handlers\GetGameAction;
use Watercooler\Api\Http\Request;
use Watercooler\Api\Http\Routing\RouteMatch;

final class GetGameActionTest extends TestCase
{
    public function testItReturnsTheGameWhenTheSlugExists(): void
    {
        $action = new GetGameAction(new LookupGameRepository());

        $response = $action(
            new Request('GET', '/api/games/synergy-report-telemetry', [], [], null),
            new RouteMatch(static fn() => null, ['slug' => 'synergy-report-telemetry']),
        );

        self::assertSame(200, $response->statusCode);
        self::assertStringContainsString('"slug": "synergy-report-telemetry"', $response->body);
    }

    public function testItReturnsNotFoundWhenTheGameDoesNotExist(): void
    {
        $action = new GetGameAction(new LookupGameRepository());

        $response = $action(
            new Request('GET', '/api/games/missing-room', [], [], null),
            new RouteMatch(static fn() => null, ['slug' => 'missing-room']),
        );

        self::assertSame(404, $response->statusCode);
        self::assertStringContainsString('game_not_found', $response->body);
    }
}

final class LookupGameRepository implements GameRepository
{
    public function slugExists(string $slug): bool
    {
        return $slug === 'synergy-report-telemetry';
    }

    public function createGame(string $slug): GameSummary
    {
        return new GameSummary(1, $slug, 'lobby', 'pre_join', 0, '2026-04-08 00:00:00');
    }

    public function findBySlug(string $slug): ?GameSummary
    {
        if ($slug !== 'synergy-report-telemetry') {
            return null;
        }

        return new GameSummary(1, $slug, 'lobby', 'pre_join', 0, '2026-04-08 00:00:00');
    }
}
