<?php

declare(strict_types=1);

namespace Watercooler\Api\Database;

use PDO;
use Watercooler\Api\Config\DatabaseConfig;
use Watercooler\Api\Games\CardSeedDefinition;
use Watercooler\Api\Games\ExecutiveSeedDefinition;
use Watercooler\Api\Games\GameSummary;
use Watercooler\Api\Games\StartGamePlayer;
use Watercooler\Api\Games\StartGameRepository;
use Watercooler\Api\Games\StartGameSetup;

final class PdoStartGameRepository implements StartGameRepository
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

    public function findPlayerBySessionToken(int $gameId, string $sessionTokenHash): ?StartGamePlayer
    {
        $statement = $this->connection()->prepare(
            <<<'SQL'
            SELECT
                gp.id AS game_player_id,
                gp.display_name,
                gp.is_host,
                gp.join_status,
                gp.office_prestige,
                gp.seat_order
            FROM game_players gp
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

        return $row === false ? null : $this->mapPlayer($row);
    }

    public function listPlayers(int $gameId): array
    {
        $statement = $this->connection()->prepare(
            <<<'SQL'
            SELECT
                gp.id AS game_player_id,
                gp.display_name,
                gp.is_host,
                gp.join_status,
                gp.office_prestige,
                gp.seat_order
            FROM game_players gp
            WHERE gp.game_id = :game_id
            ORDER BY gp.is_host DESC, gp.created_at ASC, gp.id ASC
            SQL
        );
        $statement->execute(['game_id' => $gameId]);

        /** @var list<array<string, mixed>> $rows */
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row): StartGamePlayer => $this->mapPlayer($row), $rows);
    }

    public function listAvailableCards(): array
    {
        $statement = $this->connection()->query(
            <<<'SQL'
            SELECT
                code,
                tier,
                name,
                resource_discount,
                office_prestige,
                cost_coffee,
                cost_spreadsheets,
                cost_budget,
                cost_connections,
                cost_time,
                sort_order
            FROM cards
            ORDER BY sort_order ASC, id ASC
            SQL
        );

        /** @var list<array<string, mixed>> $rows */
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            static fn(array $row): CardSeedDefinition => new CardSeedDefinition(
                code: (string) $row['code'],
                tier: (int) $row['tier'],
                name: (string) $row['name'],
                resourceDiscount: (string) $row['resource_discount'],
                officePrestige: (int) $row['office_prestige'],
                cost: [
                    'coffee' => (int) $row['cost_coffee'],
                    'spreadsheets' => (int) $row['cost_spreadsheets'],
                    'budget' => (int) $row['cost_budget'],
                    'connections' => (int) $row['cost_connections'],
                    'time' => (int) $row['cost_time'],
                ],
                sortOrder: (int) $row['sort_order'],
            ),
            $rows,
        );
    }

    public function listAvailableExecutives(): array
    {
        $statement = $this->connection()->query(
            <<<'SQL'
            SELECT
                code,
                name,
                office_prestige,
                required_coffee,
                required_spreadsheets,
                required_budget,
                required_connections,
                required_time
            FROM executives
            ORDER BY id ASC
            SQL
        );

        /** @var list<array<string, mixed>> $rows */
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            static fn(array $row): ExecutiveSeedDefinition => new ExecutiveSeedDefinition(
                code: (string) $row['code'],
                name: (string) $row['name'],
                officePrestige: (int) $row['office_prestige'],
                requirements: [
                    'coffee' => (int) $row['required_coffee'],
                    'spreadsheets' => (int) $row['required_spreadsheets'],
                    'budget' => (int) $row['required_budget'],
                    'connections' => (int) $row['required_connections'],
                    'time' => (int) $row['required_time'],
                ],
            ),
            $rows,
        );
    }

    public function persistStartedGame(int $gameId, StartGameSetup $setup): GameSummary
    {
        $connection = $this->connection();
        $connection->beginTransaction();

        try {
            $connection->prepare('DELETE FROM game_cards WHERE game_id = :game_id')->execute(['game_id' => $gameId]);
            $connection->prepare('DELETE FROM game_executives WHERE game_id = :game_id')->execute(['game_id' => $gameId]);
            $connection->prepare('DELETE FROM player_resources WHERE game_player_id IN (SELECT id FROM game_players WHERE game_id = :game_id)')
                ->execute(['game_id' => $gameId]);

            foreach ($setup->players as $player) {
                $connection->prepare(
                    'UPDATE game_players SET seat_order = :seat_order, office_prestige = 0 WHERE id = :game_player_id'
                )->execute([
                    'seat_order' => $player->seatOrder,
                    'game_player_id' => $player->gamePlayerId,
                ]);

                $connection->prepare(
                    <<<'SQL'
                    INSERT INTO player_resources (game_player_id, coffee, spreadsheets, budget, connections, time, executive_favor)
                    VALUES (:game_player_id, 0, 0, 0, 0, 0, 0)
                    SQL
                )->execute([
                    'game_player_id' => $player->gamePlayerId,
                ]);
            }

            $connection->prepare(
                <<<'SQL'
                INSERT INTO game_resource_bank (game_id, coffee, spreadsheets, budget, connections, time, executive_favor)
                VALUES (:game_id, :coffee, :spreadsheets, :budget, :connections, :time, :executive_favor)
                ON DUPLICATE KEY UPDATE
                    coffee = VALUES(coffee),
                    spreadsheets = VALUES(spreadsheets),
                    budget = VALUES(budget),
                    connections = VALUES(connections),
                    time = VALUES(time),
                    executive_favor = VALUES(executive_favor)
                SQL
            )->execute([
                'game_id' => $gameId,
                'coffee' => $setup->bank['coffee'],
                'spreadsheets' => $setup->bank['spreadsheets'],
                'budget' => $setup->bank['budget'],
                'connections' => $setup->bank['connections'],
                'time' => $setup->bank['time'],
                'executive_favor' => $setup->bank['executiveFavor'],
            ]);

            $cardIdsByCode = $this->cardIdsByCode($connection);
            foreach ($setup->marketCardsByTier as $cards) {
                foreach ($cards as $index => $card) {
                    $this->insertGameCard($connection, $gameId, $cardIdsByCode[$card->code], $card, 'market', $index + 1, null);
                }
            }

            foreach ($setup->deckCardsByTier as $cards) {
                foreach ($cards as $index => $card) {
                    $this->insertGameCard($connection, $gameId, $cardIdsByCode[$card->code], $card, 'deck', null, $index + 1);
                }
            }

            $executiveIdsByCode = $this->executiveIdsByCode($connection);
            foreach ($setup->executives as $index => $executive) {
                $connection->prepare(
                    <<<'SQL'
                    INSERT INTO game_executives (game_id, executive_id, slot_order)
                    VALUES (:game_id, :executive_id, :slot_order)
                    SQL
                )->execute([
                    'game_id' => $gameId,
                    'executive_id' => $executiveIdsByCode[$executive->code],
                    'slot_order' => $index + 1,
                ]);
            }

            $currentTurnGamePlayerId = $setup->players[0]->gamePlayerId;
            $connection->prepare(
                <<<'SQL'
                UPDATE games
                SET status = :status,
                    phase = :phase,
                    current_turn_game_player_id = :current_turn_game_player_id,
                    started_at = COALESCE(started_at, CURRENT_TIMESTAMP)
                WHERE id = :game_id
                SQL
            )->execute([
                'status' => 'active',
                'phase' => 'active',
                'current_turn_game_player_id' => $currentTurnGamePlayerId,
                'game_id' => $gameId,
            ]);

            $snapshot = [
                'currentTurnGamePlayerId' => $currentTurnGamePlayerId,
                'players' => array_map(
                    static fn(StartGamePlayer $player): array => $player->toArray(),
                    $setup->players,
                ),
                'bank' => $setup->bank,
            ];

            $connection->prepare(
                <<<'SQL'
                INSERT INTO game_events (game_id, event_type, actor_game_player_id, payload)
                VALUES (:game_id, :event_type, :actor_game_player_id, :payload)
                SQL
            )->execute([
                'game_id' => $gameId,
                'event_type' => 'game_started',
                'actor_game_player_id' => $currentTurnGamePlayerId,
                'payload' => json_encode($snapshot, JSON_UNESCAPED_SLASHES),
            ]);
            $eventId = (int) $connection->lastInsertId();

            $connection->prepare(
                <<<'SQL'
                INSERT INTO game_state_snapshots (game_id, source_event_id, snapshot_json)
                VALUES (:game_id, :source_event_id, :snapshot_json)
                SQL
            )->execute([
                'game_id' => $gameId,
                'source_event_id' => $eventId,
                'snapshot_json' => json_encode($snapshot, JSON_UNESCAPED_SLASHES),
            ]);

            $connection->commit();
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }

        return $this->findGameById($gameId)
            ?? throw new \RuntimeException('Started game could not be reloaded from storage.');
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapPlayer(array $row): StartGamePlayer
    {
        return new StartGamePlayer(
            gamePlayerId: (int) $row['game_player_id'],
            displayName: (string) $row['display_name'],
            isHost: (bool) $row['is_host'],
            joinStatus: (string) $row['join_status'],
            seatOrder: $row['seat_order'] === null ? null : (int) $row['seat_order'],
            officePrestige: (int) $row['office_prestige'],
        );
    }

    private function findGameById(int $gameId): ?GameSummary
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
            WHERE g.id = :game_id
            GROUP BY g.id, g.slug, g.status, g.phase, g.created_at
            LIMIT 1
            SQL
        );
        $statement->execute(['game_id' => $gameId]);
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

    /**
     * @return array<string, int>
     */
    private function cardIdsByCode(PDO $connection): array
    {
        $rows = $connection->query('SELECT id, code FROM cards')->fetchAll(PDO::FETCH_ASSOC);
        $map = [];

        foreach ($rows as $row) {
            $map[(string) $row['code']] = (int) $row['id'];
        }

        return $map;
    }

    /**
     * @return array<string, int>
     */
    private function executiveIdsByCode(PDO $connection): array
    {
        $rows = $connection->query('SELECT id, code FROM executives')->fetchAll(PDO::FETCH_ASSOC);
        $map = [];

        foreach ($rows as $row) {
            $map[(string) $row['code']] = (int) $row['id'];
        }

        return $map;
    }

    private function insertGameCard(
        PDO $connection,
        int $gameId,
        int $cardId,
        CardSeedDefinition $card,
        string $location,
        ?int $marketSlot,
        ?int $deckPosition,
    ): void {
        $connection->prepare(
            <<<'SQL'
            INSERT INTO game_cards (game_id, card_id, tier, location, deck_position, market_slot)
            VALUES (:game_id, :card_id, :tier, :location, :deck_position, :market_slot)
            SQL
        )->execute([
            'game_id' => $gameId,
            'card_id' => $cardId,
            'tier' => $card->tier,
            'location' => $location,
            'deck_position' => $deckPosition,
            'market_slot' => $marketSlot,
        ]);
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
