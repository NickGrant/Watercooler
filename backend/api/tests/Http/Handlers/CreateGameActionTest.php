<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Http\Handlers;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Games\CreateGameService;
use Watercooler\Api\Games\GameRepository;
use Watercooler\Api\Games\GameSummary;
use Watercooler\Api\Games\SlugGenerator;
use Watercooler\Api\Http\Handlers\CreateGameAction;
use Watercooler\Api\Http\Request;
use Watercooler\Api\Http\Routing\RouteMatch;

final class CreateGameActionTest extends TestCase
{
    public function testItReturnsCreatedGamePayload(): void
    {
        $action = new CreateGameAction(
            new CreateGameService(
                new HandlerGameRepository(),
                new HandlerSlugGenerator(),
            ),
        );

        $response = $action(
            new Request('POST', '/api/games', [], [], null),
            new RouteMatch(static fn() => null, []),
        );

        self::assertSame(201, $response->statusCode);
        self::assertStringContainsString('"slug": "synergy-report-telemetry"', $response->body);
    }
}

final class HandlerGameRepository implements GameRepository
{
    public function slugExists(string $slug): bool
    {
        return false;
    }

    public function createGame(string $slug): GameSummary
    {
        return new GameSummary(1, $slug, 'lobby', 'pre_join', 0, '2026-04-08 00:00:00');
    }

    public function findBySlug(string $slug): ?GameSummary
    {
        return null;
    }
}

final class HandlerSlugGenerator implements SlugGenerator
{
    public function generateCandidate(): string
    {
        return 'synergy-report-telemetry';
    }
}
