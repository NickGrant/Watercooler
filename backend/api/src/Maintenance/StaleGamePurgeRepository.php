<?php

declare(strict_types=1);

namespace Watercooler\Api\Maintenance;

interface StaleGamePurgeRepository
{
    /**
     * @return list<StaleGameRecord>
     */
    public function purgeOlderThan(\DateTimeImmutable $cutoff): array;
}
