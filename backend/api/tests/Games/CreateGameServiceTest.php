<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Games;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Games\CreateGameService;
use Watercooler\Api\Games\GameRepository;
use Watercooler\Api\Games\GameSummary;
use Watercooler\Api\Games\SlugGenerator;

final class CreateGameServiceTest extends TestCase
{
    public function testItCreatesAGameWithTheFirstAvailableSlug(): void
    {
        $repository = new InMemoryGameRepository();
        $service = new CreateGameService($repository, new PredictableSlugGenerator(['synergy-report-telemetry']));

        $game = $service->createGame();

        self::assertSame('synergy-report-telemetry', $game->slug);
        self::assertTrue($repository->slugExists('synergy-report-telemetry'));
    }

    public function testItAppendsANumericSuffixAfterRepeatedCollisions(): void
    {
        $repository = new InMemoryGameRepository();

        for ($i = 0; $i < 10; $i++) {
            $repository->createGame('synergy-report-telemetry');
        }

        $service = new CreateGameService(
            $repository,
            new PredictableSlugGenerator(array_fill(0, 11, 'synergy-report-telemetry')),
        );

        $game = $service->createGame();

        self::assertMatchesRegularExpression('/^synergy-report-telemetry-\d+$/', $game->slug);
    }
}

final class InMemoryGameRepository implements GameRepository
{
    /** @var array<string, GameSummary> */
    private array $games = [];
    private int $nextId = 1;

    public function slugExists(string $slug): bool
    {
        return isset($this->games[$slug]);
    }

    public function createGame(string $slug): GameSummary
    {
        $game = new GameSummary(
            id: $this->nextId++,
            slug: $slug,
            status: 'lobby',
            phase: 'pre_join',
            playerCount: 0,
            createdAt: '2026-04-08 00:00:00',
        );

        $this->games[$slug] = $game;

        return $game;
    }

    public function findBySlug(string $slug): ?GameSummary
    {
        return $this->games[$slug] ?? null;
    }
}

final class PredictableSlugGenerator implements SlugGenerator
{
    /** @param list<string> $candidates */
    public function __construct(
        private array $candidates,
    ) {
    }

    public function generateCandidate(): string
    {
        return array_shift($this->candidates) ?? 'fallback-slug-value';
    }
}
