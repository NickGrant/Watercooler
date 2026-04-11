<?php

declare(strict_types=1);

namespace Watercooler\Api\BugReports;

final class BugReportReceipt
{
    public function __construct(
        public readonly int $id,
        public readonly string $status,
        public readonly string $createdAt,
    ) {
    }
}
