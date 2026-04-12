<?php

declare(strict_types=1);

namespace Watercooler\Api\Maintenance;

final class StaleGamePurgeService
{
    public function __construct(
        private readonly StaleGamePurgeRepository $repository,
    ) {
    }

    public function purgeInactiveGames(
        \DateTimeImmutable $now,
        \DateInterval $maxAge = new \DateInterval('PT48H'),
    ): StaleGamePurgeResult {
        $cutoff = $now->sub($maxAge);
        $purgedGames = $this->repository->purgeOlderThan($cutoff);

        return new StaleGamePurgeResult(
            cutoff: $cutoff,
            purgedGameCount: count($purgedGames),
            slugs: array_map(
                static fn(StaleGameRecord $game): string => $game->slug,
                $purgedGames,
            ),
        );
    }
}
