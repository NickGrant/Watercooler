<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Database;

use PDO;
use Watercooler\Realtime\Config\DatabaseConfig;
use Watercooler\Realtime\Lobby\AvatarView;
use Watercooler\Realtime\Lobby\JoinRoomRepository;
use Watercooler\Realtime\Lobby\LobbyParticipant;

final class PdoJoinRoomRepository implements JoinRoomRepository
{
    private ?PDO $connection = null;

    public function __construct(
        private readonly DatabaseConfig $config,
    ) {
    }

    public function findParticipantBySessionToken(string $slug, string $sessionTokenHash): ?LobbyParticipant
    {
        $statement = $this->connection()->prepare(
            <<<'SQL'
            SELECT
                g.id AS game_id,
                g.slug,
                gp.id AS game_player_id,
                gp.player_id,
                gp.display_name,
                gp.is_host,
                gp.join_status,
                pa.body_option,
                pa.face_option,
                pa.hair_option
            FROM games g
            INNER JOIN game_players gp ON gp.game_id = g.id
            INNER JOIN player_avatars pa ON pa.player_id = gp.player_id
            WHERE g.slug = :slug
              AND gp.session_token_hash = :session_token_hash
              AND g.status IN ('lobby', 'active', 'completed')
              AND g.phase IN ('pre_join', 'lobby', 'active', 'endgame', 'completed')
            LIMIT 1
            SQL
        );
        $statement->execute([
            'slug' => $slug,
            'session_token_hash' => $sessionTokenHash,
        ]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->mapParticipant($row);
    }

    public function listParticipants(int $gameId): array
    {
        $statement = $this->connection()->prepare(
            <<<'SQL'
            SELECT
                g.id AS game_id,
                g.slug,
                gp.id AS game_player_id,
                gp.player_id,
                gp.display_name,
                gp.is_host,
                gp.join_status,
                pa.body_option,
                pa.face_option,
                pa.hair_option
            FROM games g
            INNER JOIN game_players gp ON gp.game_id = g.id
            INNER JOIN player_avatars pa ON pa.player_id = gp.player_id
            WHERE g.id = :game_id
            ORDER BY gp.is_host DESC, gp.created_at ASC, gp.id ASC
            SQL
        );
        $statement->execute(['game_id' => $gameId]);

        /** @var list<array<string, mixed>> $rows */
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row): LobbyParticipant => $this->mapParticipant($row), $rows);
    }

    public function markConnected(int $gamePlayerId): void
    {
        $statement = $this->connection()->prepare(
            'UPDATE game_players SET join_status = :join_status WHERE id = :game_player_id'
        );
        $statement->execute([
            'join_status' => 'connected',
            'game_player_id' => $gamePlayerId,
        ]);
    }

    public function markDisconnected(int $gamePlayerId): void
    {
        $statement = $this->connection()->prepare(
            'UPDATE game_players SET join_status = :join_status WHERE id = :game_player_id'
        );
        $statement->execute([
            'join_status' => 'disconnected',
            'game_player_id' => $gamePlayerId,
        ]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapParticipant(array $row): LobbyParticipant
    {
        return new LobbyParticipant(
            gameId: (int) $row['game_id'],
            gamePlayerId: (int) $row['game_player_id'],
            playerId: (int) $row['player_id'],
            gameSlug: (string) $row['slug'],
            displayName: (string) $row['display_name'],
            isHost: (bool) $row['is_host'],
            joinStatus: (string) $row['join_status'],
            avatar: new AvatarView(
                body: (string) $row['body_option'],
                face: (string) $row['face_option'],
                hair: (string) $row['hair_option'],
            ),
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
