<?php

declare(strict_types=1);

namespace Watercooler\Api\Database;

use PDO;
use Watercooler\Api\Config\DatabaseConfig;
use Watercooler\Api\Games\GameRepository;
use Watercooler\Api\Games\GameSummary;

final class PdoGameRepository implements GameRepository
{
    private ?PDO $connection = null;

    public function __construct(
        private readonly DatabaseConfig $config,
    ) {
    }

    public function slugExists(string $slug): bool
    {
        $statement = $this->connection()->prepare('SELECT COUNT(*) FROM games WHERE slug = :slug');
        $statement->execute(['slug' => $slug]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function createGame(string $slug): GameSummary
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO games (slug, status, phase) VALUES (:slug, :status, :phase)'
        );
        $statement->execute([
            'slug' => $slug,
            'status' => 'lobby',
            'phase' => 'pre_join',
        ]);

        return $this->findBySlug($slug)
            ?? throw new \RuntimeException('Created game could not be loaded back from storage.');
    }

    public function findBySlug(string $slug): ?GameSummary
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
