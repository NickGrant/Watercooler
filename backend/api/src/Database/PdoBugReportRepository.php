<?php

declare(strict_types=1);

namespace Watercooler\Api\Database;

use PDO;
use Watercooler\Api\BugReports\BugReportContext;
use Watercooler\Api\BugReports\BugReportContextRepository;
use Watercooler\Api\BugReports\BugReportReceipt;
use Watercooler\Api\BugReports\BugReportRepository;
use Watercooler\Api\BugReports\BugReportSubmission;
use Watercooler\Api\Config\DatabaseConfig;

final class PdoBugReportRepository implements BugReportRepository, BugReportContextRepository
{
    private ?PDO $connection = null;

    public function __construct(
        private readonly DatabaseConfig $config,
        ?PDO $connection = null,
    ) {
        $this->connection = $connection;
    }

    public function create(BugReportSubmission $submission): BugReportReceipt
    {
        $statement = $this->connection()->prepare(
            <<<'SQL'
            INSERT INTO bug_reports (
                game_id,
                reporter_game_player_id,
                current_turn_game_player_id,
                room_slug,
                reporter_display_name,
                reporter_seat_order,
                reply_email,
                message_body,
                status,
                game_status_snapshot,
                game_phase_snapshot,
                client_user_agent
            ) VALUES (
                :game_id,
                :reporter_game_player_id,
                :current_turn_game_player_id,
                :room_slug,
                :reporter_display_name,
                :reporter_seat_order,
                :reply_email,
                :message_body,
                'unread',
                :game_status_snapshot,
                :game_phase_snapshot,
                :client_user_agent
            )
            SQL
        );
        $statement->execute([
            'game_id' => $submission->gameId,
            'reporter_game_player_id' => $submission->reporterGamePlayerId,
            'current_turn_game_player_id' => $submission->currentTurnGamePlayerId,
            'room_slug' => $submission->roomSlug,
            'reporter_display_name' => $submission->reporterDisplayName,
            'reporter_seat_order' => $submission->reporterSeatOrder,
            'reply_email' => $submission->replyEmail,
            'message_body' => $submission->message,
            'game_status_snapshot' => $submission->gameStatusSnapshot,
            'game_phase_snapshot' => $submission->gamePhaseSnapshot,
            'client_user_agent' => $submission->clientUserAgent,
        ]);

        $id = (int) $this->connection()->lastInsertId();
        $createdAt = (string) $this->connection()
            ->query('SELECT created_at FROM bug_reports WHERE id = ' . $id)
            ->fetchColumn();

        return new BugReportReceipt(
            id: $id,
            status: 'unread',
            createdAt: $createdAt,
        );
    }

    public function findReporterContext(int $gameId, string $sessionTokenHash): ?BugReportContext
    {
        $statement = $this->connection()->prepare(
            <<<'SQL'
            SELECT
                gp.id AS reporter_game_player_id,
                gp.display_name,
                gp.seat_order,
                g.current_turn_game_player_id
            FROM games g
            INNER JOIN game_players gp ON gp.game_id = g.id
            WHERE g.id = :game_id
              AND gp.session_token_hash = :session_token_hash
            LIMIT 1
            SQL
        );
        $statement->execute([
            'game_id' => $gameId,
            'session_token_hash' => $sessionTokenHash,
        ]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return new BugReportContext(
            reporterGamePlayerId: (int) $row['reporter_game_player_id'],
            reporterDisplayName: (string) $row['display_name'],
            reporterSeatOrder: $row['seat_order'] === null ? null : (int) $row['seat_order'],
            currentTurnGamePlayerId: $row['current_turn_game_player_id'] === null
                ? null
                : (int) $row['current_turn_game_player_id'],
        );
    }

    private function connection(): PDO
    {
        if ($this->connection === null) {
            $this->connection = new PDO(
                sprintf(
                    'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                    $this->config->host,
                    $this->config->port,
                    $this->config->name,
                ),
                $this->config->user,
                $this->config->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ],
            );
        }

        return $this->connection;
    }
}
