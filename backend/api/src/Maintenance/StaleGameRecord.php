<?php

declare(strict_types=1);

namespace Watercooler\Api\Maintenance;

final class StaleGameRecord
{
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
    ) {
    }
}
