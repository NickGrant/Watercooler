<?php

declare(strict_types=1);

namespace Watercooler\Api\Database;

use PDO;
use CtrlStudio\GameApi\Config\DatabaseConfig;
use CtrlStudio\GameApi\Database\PdoFactory;
use Watercooler\Api\Maintenance\StaleGamePurgeRepository;
use Watercooler\Api\Maintenance\StaleGameRecord;

final class PdoStaleGamePurgeRepository implements StaleGamePurgeRepository
{
    private ?PDO $connection = null;

    public function __construct(
        private readonly DatabaseConfig $config,
    ) {
    }

    public function purgeOlderThan(\DateTimeImmutable $cutoff): array
    {
        $connection = $this->connection();
        $connection->beginTransaction();

        try {
            $selectStatement = $connection->prepare(
                'SELECT id, slug FROM games WHERE updated_at < :cutoff ORDER BY updated_at ASC'
            );
            $selectStatement->execute([
                'cutoff' => $cutoff->format('Y-m-d H:i:s'),
            ]);

            /** @var list<array{id:string|int, slug:string}> $rows */
            $rows = $selectStatement->fetchAll(PDO::FETCH_ASSOC);
            $purgedGames = array_map(
                static fn(array $row): StaleGameRecord => new StaleGameRecord(
                    id: (int) $row['id'],
                    slug: (string) $row['slug'],
                ),
                $rows,
            );

            if ($purgedGames !== []) {
                $deleteStatement = $connection->prepare(
                    'DELETE FROM games WHERE updated_at < :cutoff'
                );
                $deleteStatement->execute([
                    'cutoff' => $cutoff->format('Y-m-d H:i:s'),
                ]);
            }

            $connection->exec(
                <<<'SQL'
                DELETE players
                FROM players
                LEFT JOIN game_players ON game_players.player_id = players.id
                WHERE game_players.id IS NULL
                SQL
            );

            $connection->commit();

            return $purgedGames;
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    private function connection(): PDO
    {
        if ($this->connection === null) {
            $this->connection = PdoFactory::create($this->config);
        }

        return $this->connection;
    }
}
