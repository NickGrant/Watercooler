<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Maintenance;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Maintenance\StaleGamePurgeRepository;
use Watercooler\Api\Maintenance\StaleGamePurgeService;
use Watercooler\Api\Maintenance\StaleGameRecord;

final class StaleGamePurgeServiceTest extends TestCase
{
    public function testItPurgesGamesOlderThanFortyEightHours(): void
    {
        $repository = new RecordingStaleGamePurgeRepository([
            new StaleGameRecord(1, 'coffee-summit'),
            new StaleGameRecord(2, 'budget-bonfire'),
        ]);
        $service = new StaleGamePurgeService($repository);
        $now = new \DateTimeImmutable('2026-04-11 12:00:00');

        $result = $service->purgeInactiveGames($now);

        self::assertSame('2026-04-09 12:00:00', $repository->lastCutoff?->format('Y-m-d H:i:s'));
        self::assertSame(2, $result->purgedGameCount);
        self::assertSame(['coffee-summit', 'budget-bonfire'], $result->slugs);
    }

    public function testItReturnsAnEmptyResultWhenNoGamesNeedPurging(): void
    {
        $repository = new RecordingStaleGamePurgeRepository([]);
        $service = new StaleGamePurgeService($repository);

        $result = $service->purgeInactiveGames(new \DateTimeImmutable('2026-04-11 12:00:00'));

        self::assertSame(0, $result->purgedGameCount);
        self::assertSame([], $result->slugs);
    }
}

final class RecordingStaleGamePurgeRepository implements StaleGamePurgeRepository
{
    public ?\DateTimeImmutable $lastCutoff = null;

    /**
     * @param list<StaleGameRecord> $gamesToReturn
     */
    public function __construct(
        private readonly array $gamesToReturn,
    ) {
    }

    public function purgeOlderThan(\DateTimeImmutable $cutoff): array
    {
        $this->lastCutoff = $cutoff;

        return $this->gamesToReturn;
    }
}
