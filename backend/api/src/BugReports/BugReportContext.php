<?php

declare(strict_types=1);

namespace Watercooler\Api\BugReports;

final class BugReportContext
{
    public function __construct(
        public readonly int $reporterGamePlayerId,
        public readonly string $reporterDisplayName,
        public readonly ?int $reporterSeatOrder,
        public readonly ?int $currentTurnGamePlayerId,
    ) {
    }
}
