<?php

declare(strict_types=1);

namespace Watercooler\Api\BugReports;

interface BugReportContextRepository
{
    public function findReporterContext(int $gameId, string $sessionTokenHash): ?BugReportContext;
}
