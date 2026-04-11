<?php

declare(strict_types=1);

namespace Watercooler\Api\BugReports;

interface BugReportRepository
{
    public function create(BugReportSubmission $submission): BugReportReceipt;
}
