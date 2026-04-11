<?php

declare(strict_types=1);

namespace Watercooler\Api\BugReports;

final class BugReportSubmission
{
    public function __construct(
        public readonly int $gameId,
        public readonly string $roomSlug,
        public readonly ?int $reporterGamePlayerId,
        public readonly string $reporterDisplayName,
        public readonly ?int $reporterSeatOrder,
        public readonly ?string $replyEmail,
        public readonly string $message,
        public readonly string $gameStatusSnapshot,
        public readonly string $gamePhaseSnapshot,
        public readonly ?int $currentTurnGamePlayerId,
        public readonly ?string $clientUserAgent,
    ) {
    }
}
