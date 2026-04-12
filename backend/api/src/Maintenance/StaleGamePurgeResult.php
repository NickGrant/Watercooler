<?php

declare(strict_types=1);

namespace Watercooler\Api\Maintenance;

final class StaleGamePurgeResult
{
    /**
     * @param list<string> $slugs
     */
    public function __construct(
        public readonly \DateTimeImmutable $cutoff,
        public readonly int $purgedGameCount,
        public readonly array $slugs,
    ) {
    }
}
