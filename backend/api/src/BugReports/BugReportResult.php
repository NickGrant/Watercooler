<?php

declare(strict_types=1);

namespace Watercooler\Api\BugReports;

final class BugReportResult
{
    public function __construct(
        public readonly BugReportReceipt $receipt,
    ) {
    }

    /**
     * @return array{
     *     id: int,
     *     status: string,
     *     createdAt: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->receipt->id,
            'status' => $this->receipt->status,
            'createdAt' => $this->receipt->createdAt,
        ];
    }
}
