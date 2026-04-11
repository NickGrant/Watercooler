<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Http\Handlers;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\BugReports\BugReportContext;
use Watercooler\Api\BugReports\BugReportContextRepository;
use Watercooler\Api\BugReports\BugReportReceipt;
use Watercooler\Api\BugReports\BugReportRepository;
use Watercooler\Api\BugReports\BugReportService;
use Watercooler\Api\BugReports\BugReportSubmission;
use Watercooler\Api\Games\GameRepository;
use Watercooler\Api\Games\GameSummary;
use Watercooler\Api\Http\Handlers\SubmitBugReportAction;
use Watercooler\Api\Http\Request;
use Watercooler\Api\Http\Routing\RouteMatch;

final class SubmitBugReportActionTest extends TestCase
{
    public function testItReturnsCreatedBugReportPayload(): void
    {
        $action = new SubmitBugReportAction(
            new BugReportService(
                new HandlerBugReportGameRepository(),
                new HandlerBugReportContextRepository(),
                new HandlerBugReportRepository(),
            ),
        );

        $response = $action(
            new Request(
                'POST',
                '/api/games/synergy-report-telemetry/bug-reports',
                ['user-agent' => 'Karma'],
                [],
                [
                    'sessionToken' => 'host-token',
                    'message' => 'The turn banner stayed on the wrong player.',
                ],
            ),
            new RouteMatch(static fn() => null, ['slug' => 'synergy-report-telemetry']),
        );

        self::assertSame(201, $response->statusCode);
        self::assertStringContainsString('"status": "unread"', $response->body);
        self::assertStringContainsString('"id": 11', $response->body);
    }

    public function testItReturnsValidationErrorsAsJson(): void
    {
        $action = new SubmitBugReportAction(
            new BugReportService(
                new HandlerBugReportGameRepository(),
                new HandlerBugReportContextRepository(),
                new HandlerBugReportRepository(),
            ),
        );

        $response = $action(
            new Request(
                'POST',
                '/api/games/synergy-report-telemetry/bug-reports',
                [],
                [],
                [
                    'sessionToken' => 'host-token',
                    'message' => '',
                ],
            ),
            new RouteMatch(static fn() => null, ['slug' => 'synergy-report-telemetry']),
        );

        self::assertSame(422, $response->statusCode);
        self::assertStringContainsString('message_required', $response->body);
    }
}

final class HandlerBugReportGameRepository implements GameRepository
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

final class HandlerBugReportContextRepository implements BugReportContextRepository
{
    public function findReporterContext(int $gameId, string $sessionTokenHash): ?BugReportContext
    {
        return $sessionTokenHash === hash('sha256', 'host-token')
            ? new BugReportContext(5, 'Pam', 1, 5)
            : null;
    }
}

final class HandlerBugReportRepository implements BugReportRepository
{
    public function create(BugReportSubmission $submission): BugReportReceipt
    {
        return new BugReportReceipt(11, 'unread', '2026-04-10 23:30:00');
    }
}
