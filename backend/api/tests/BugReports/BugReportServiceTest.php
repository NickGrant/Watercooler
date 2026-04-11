<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\BugReports;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\BugReports\BugReportContext;
use Watercooler\Api\BugReports\BugReportContextRepository;
use Watercooler\Api\BugReports\BugReportException;
use Watercooler\Api\BugReports\BugReportReceipt;
use Watercooler\Api\BugReports\BugReportRepository;
use Watercooler\Api\BugReports\BugReportService;
use Watercooler\Api\BugReports\BugReportSubmission;
use Watercooler\Api\Games\GameRepository;
use Watercooler\Api\Games\GameSummary;

final class BugReportServiceTest extends TestCase
{
    public function testItValidatesAndPersistsABugReport(): void
    {
        $repository = new InMemoryBugReportRepository();
        $service = new BugReportService(
            new InMemoryBugReportGameRepository(),
            new InMemoryBugReportContextRepository(),
            $repository,
        );

        $result = $service->submit(
            'synergy-report-telemetry',
            [
                'sessionToken' => 'host-token',
                'replyEmail' => 'pam@example.com',
                'message' => 'The executive row did not refresh after my purchase.',
            ],
            'Browser UA',
        );

        self::assertSame(77, $result->receipt->id);
        self::assertSame('unread', $result->receipt->status);
        self::assertSame('Pam', $repository->lastSubmission?->reporterDisplayName);
        self::assertSame(2, $repository->lastSubmission?->reporterSeatOrder);
        self::assertSame('active', $repository->lastSubmission?->gameStatusSnapshot);
        self::assertSame('active', $repository->lastSubmission?->gamePhaseSnapshot);
        self::assertSame(3, $repository->lastSubmission?->currentTurnGamePlayerId);
        self::assertSame('Browser UA', $repository->lastSubmission?->clientUserAgent);
    }

    public function testItRequiresAMessage(): void
    {
        $service = new BugReportService(
            new InMemoryBugReportGameRepository(),
            new InMemoryBugReportContextRepository(),
            new InMemoryBugReportRepository(),
        );

        $this->expectException(BugReportException::class);
        $this->expectExceptionMessage('A short description of the bug is required before submitting a report.');

        $service->submit(
            'synergy-report-telemetry',
            [
                'sessionToken' => 'host-token',
                'message' => '   ',
            ],
            null,
        );
    }
}

final class InMemoryBugReportGameRepository implements GameRepository
{
    public function slugExists(string $slug): bool
    {
        return false;
    }

    public function createGame(string $slug): GameSummary
    {
        throw new \BadMethodCallException('Not needed in this test.');
    }

    public function findBySlug(string $slug): ?GameSummary
    {
        return $slug === 'synergy-report-telemetry'
            ? new GameSummary(1, $slug, 'active', 'active', 2, '2026-04-08 00:00:00')
            : null;
    }
}

final class InMemoryBugReportContextRepository implements BugReportContextRepository
{
    public function findReporterContext(int $gameId, string $sessionTokenHash): ?BugReportContext
    {
        if ($gameId !== 1 || $sessionTokenHash !== hash('sha256', 'host-token')) {
            return null;
        }

        return new BugReportContext(4, 'Pam', 2, 3);
    }
}

final class InMemoryBugReportRepository implements BugReportRepository
{
    public ?BugReportSubmission $lastSubmission = null;

    public function create(BugReportSubmission $submission): BugReportReceipt
    {
        $this->lastSubmission = $submission;

        return new BugReportReceipt(77, 'unread', '2026-04-10 23:00:00');
    }
}
