<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Database;

use PDO;
use PHPUnit\Framework\TestCase;
use Watercooler\Api\BugReports\BugReportSubmission;
use Watercooler\Api\Config\DatabaseConfig;
use Watercooler\Api\Database\PdoBugReportRepository;

final class PdoBugReportRepositoryTest extends TestCase
{
    public function testItPersistsUnreadBugReportsWithTriageContext(): void
    {
        $connection = new PDO('sqlite::memory:');
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createSchema($connection);

        $repository = new PdoBugReportRepository(
            new DatabaseConfig('localhost', 3306, 'watercooler', 'user', 'password'),
            $connection,
        );

        $receipt = $repository->create(
            new BugReportSubmission(
                gameId: 1,
                roomSlug: 'synergy-report-telemetry',
                reporterGamePlayerId: 4,
                reporterDisplayName: 'Pam',
                reporterSeatOrder: 2,
                replyEmail: 'pam@example.com',
                message: 'The backlog briefly showed a duplicate card after I claimed one.',
                gameStatusSnapshot: 'active',
                gamePhaseSnapshot: 'active',
                currentTurnGamePlayerId: 3,
                clientUserAgent: 'ChromeHeadless Test',
            ),
        );

        $row = $connection
            ->query('SELECT * FROM bug_reports WHERE id = 1')
            ->fetch(PDO::FETCH_ASSOC);

        self::assertNotFalse($row);
        self::assertSame(1, $receipt->id);
        self::assertSame('unread', $receipt->status);
        self::assertSame('synergy-report-telemetry', $row['room_slug']);
        self::assertSame('Pam', $row['reporter_display_name']);
        self::assertSame(2, (int) $row['reporter_seat_order']);
        self::assertSame('pam@example.com', $row['reply_email']);
        self::assertSame('active', $row['game_status_snapshot']);
        self::assertSame('active', $row['game_phase_snapshot']);
        self::assertSame(3, (int) $row['current_turn_game_player_id']);
        self::assertSame('ChromeHeadless Test', $row['client_user_agent']);
        self::assertSame(
            'The backlog briefly showed a duplicate card after I claimed one.',
            $row['message_body'],
        );
        self::assertSame('unread', $row['status']);
        self::assertNotSame('', $receipt->createdAt);
    }

    private function createSchema(PDO $connection): void
    {
        $connection->exec(
            <<<'SQL'
            CREATE TABLE bug_reports (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                game_id INTEGER NOT NULL,
                reporter_game_player_id INTEGER NULL,
                current_turn_game_player_id INTEGER NULL,
                room_slug TEXT NOT NULL,
                reporter_display_name TEXT NOT NULL,
                reporter_seat_order INTEGER NULL,
                reply_email TEXT NULL,
                message_body TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT 'unread',
                game_status_snapshot TEXT NOT NULL,
                game_phase_snapshot TEXT NOT NULL,
                client_user_agent TEXT NULL,
                read_at TEXT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            SQL
        );
    }
}
