<?php

declare(strict_types=1);

namespace Watercooler\Api\Database;

use PDO;
use Watercooler\Api\Config\DatabaseConfig;
use Watercooler\Api\Games\GameSummary;
use Watercooler\Api\Players\AvatarSelection;
use Watercooler\Api\Players\JoinBootstrapRepository;
use Watercooler\Api\Players\JoinedPlayer;

final class PdoJoinBootstrapRepository implements JoinBootstrapRepository
{
    private ?PDO $connection = null;

    public function __construct(
        private readonly DatabaseConfig $config,
    ) {
    }

    public function findGameBySlug(string $slug): ?GameSummary
    {
        $statement = $this->connection()->prepare(
            <<<'SQL'
            SELECT
                g.id,
                g.slug,
                g.status,
                g.phase,
                g.created_at,
                COUNT(gp.id) AS player_count
            FROM games g
            LEFT JOIN game_players gp ON gp.game_id = g.id
            WHERE g.slug = :slug
            GROUP BY g.id, g.slug, g.status, g.phase, g.created_at
            LIMIT 1
            SQL
        );
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return new GameSummary(
            id: (int) $row['id'],
            slug: (string) $row['slug'],
            status: (string) $row['status'],
            phase: (string) $row['phase'],
            playerCount: (int) $row['player_count'],
            createdAt: (string) $row['created_at'],
        );
    }

    public function displayNameExists(int $gameId, string $displayName): bool
    {
        $statement = $this->connection()->prepare(
            'SELECT COUNT(*) FROM game_players WHERE game_id = :game_id AND display_name = :display_name'
        );
        $statement->execute([
            'game_id' => $gameId,
            'display_name' => $displayName,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function findPlayerBySessionToken(int $gameId, string $sessionTokenHash): ?JoinedPlayer
    {
        $statement = $this->connection()->prepare(
            <<<'SQL'
            SELECT
                gp.id AS game_player_id,
                gp.player_id,
                gp.display_name,
                gp.is_host,
                gp.join_status,
                pa.avatar_option
            FROM game_players gp
            INNER JOIN player_avatars pa ON pa.player_id = gp.player_id
            WHERE gp.game_id = :game_id
              AND gp.session_token_hash = :session_token_hash
            LIMIT 1
            SQL
        );
        $statement->execute([
            'game_id' => $gameId,
            'session_token_hash' => $sessionTokenHash,
        ]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->mapJoinedPlayer($row);
    }

    public function createJoinedPlayer(
        int $gameId,
        string $displayName,
        AvatarSelection $avatar,
        string $sessionTokenHash,
    ): JoinedPlayer {
        $connection = $this->connection();
        $connection->beginTransaction();

        try {
            $connection->prepare('INSERT INTO players () VALUES ()')->execute();
            $playerId = (int) $connection->lastInsertId();

            $connection->prepare(
                <<<'SQL'
                INSERT INTO player_avatars (player_id, avatar_option)
                VALUES (:player_id, :avatar_option)
                SQL
            )->execute([
                'player_id' => $playerId,
                // BEGIN AGENT CHANGE
                'avatar_option' => $avatar->id,
                // END AGENT CHANGE
            ]);

            $hostGamePlayerId = $this->findHostGamePlayerId($gameId);
            $isHost = $hostGamePlayerId === null;

            $connection->prepare(
                <<<'SQL'
                INSERT INTO game_players (game_id, player_id, display_name, is_host, join_status, session_token_hash)
                VALUES (:game_id, :player_id, :display_name, :is_host, :join_status, :session_token_hash)
                SQL
            )->execute([
                'game_id' => $gameId,
                'player_id' => $playerId,
                'display_name' => $displayName,
                'is_host' => $isHost ? 1 : 0,
                'join_status' => 'joined',
                'session_token_hash' => $sessionTokenHash,
            ]);

            $gamePlayerId = (int) $connection->lastInsertId();

            $connection->prepare(
                'UPDATE games SET phase = :phase, host_game_player_id = COALESCE(host_game_player_id, :host_game_player_id) WHERE id = :game_id'
            )->execute([
                'phase' => 'lobby',
                'host_game_player_id' => $gamePlayerId,
                'game_id' => $gameId,
            ]);

            $connection->commit();

            return new JoinedPlayer(
                gamePlayerId: $gamePlayerId,
                playerId: $playerId,
                displayName: $displayName,
                isHost: $isHost,
                joinStatus: 'joined',
                avatar: $avatar,
            );
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    public function listPlayers(int $gameId): array
    {
        $statement = $this->connection()->prepare(
            <<<'SQL'
            SELECT
                gp.id AS game_player_id,
                gp.player_id,
                gp.display_name,
                gp.is_host,
                gp.join_status,
                pa.avatar_option
            FROM game_players gp
            INNER JOIN player_avatars pa ON pa.player_id = gp.player_id
            WHERE gp.game_id = :game_id
            ORDER BY gp.is_host DESC, gp.created_at ASC, gp.id ASC
            SQL
        );
        $statement->execute(['game_id' => $gameId]);

        /** @var list<array<string, mixed>> $rows */
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row): JoinedPlayer => $this->mapJoinedPlayer($row), $rows);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapJoinedPlayer(array $row): JoinedPlayer
    {
        return new JoinedPlayer(
            gamePlayerId: (int) $row['game_player_id'],
            playerId: (int) $row['player_id'],
            displayName: (string) $row['display_name'],
            isHost: (bool) $row['is_host'],
            joinStatus: (string) $row['join_status'],
            avatar: new AvatarSelection(
                // BEGIN AGENT CHANGE
                id: (string) $row['avatar_option'],
                // END AGENT CHANGE
            ),
        );
    }

    private function findHostGamePlayerId(int $gameId): ?int
    {
        $statement = $this->connection()->prepare(
            'SELECT host_game_player_id FROM games WHERE id = :game_id LIMIT 1'
        );
        $statement->execute(['game_id' => $gameId]);
        $value = $statement->fetchColumn();

        return $value === false || $value === null ? null : (int) $value;
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
