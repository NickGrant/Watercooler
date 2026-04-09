<?php

declare(strict_types=1);

namespace Watercooler\Api\Database;

use PDO;
use Watercooler\Api\Config\DatabaseConfig;
use Watercooler\Api\Games\ActiveGamePlayer;
use Watercooler\Api\Games\ActiveGameState;
use Watercooler\Api\Games\CardSeedDefinition;
use Watercooler\Api\Games\ClaimProjectRepository;
use Watercooler\Api\Games\EndgameResolver;
use Watercooler\Api\Games\ExecutiveSeedDefinition;
use Watercooler\Api\Games\GameSummary;
use Watercooler\Api\Games\GameStateProjectionRepository;
use Watercooler\Api\Games\PlayerResourceSet;
use Watercooler\Api\Games\PlayerCardView;
use Watercooler\Api\Games\PlayerExecutiveView;
use Watercooler\Api\Games\PurchaseAdvantageRepository;
use Watercooler\Api\Games\StartGamePlayer;
use Watercooler\Api\Games\TakeResourcesRepository;

final class PdoTakeResourcesRepository implements TakeResourcesRepository, ClaimProjectRepository, PurchaseAdvantageRepository, GameStateProjectionRepository
{
    private ?PDO $connection = null;

    public function __construct(
        private readonly DatabaseConfig $config,
        private readonly EndgameResolver $endgameResolver = new EndgameResolver(),
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

        if ($row === false || $row['seat_order'] === null) {
            return null;
        }

        return new StartGamePlayer(
            gamePlayerId: (int) $row['game_player_id'],
            displayName: (string) $row['display_name'],
            isHost: (bool) $row['is_host'],
            joinStatus: (string) $row['join_status'],
            seatOrder: (int) $row['seat_order'],
            officePrestige: (int) $row['office_prestige'],
        );
    }

    public function loadState(int $gameId): ActiveGameState
    {
        $gameStatement = $this->connection()->prepare(
            'SELECT current_turn_game_player_id FROM games WHERE id = :game_id LIMIT 1'
        );
        $gameStatement->execute(['game_id' => $gameId]);
        $gameRow = $gameStatement->fetch(PDO::FETCH_ASSOC);

        if ($gameRow === false || $gameRow['current_turn_game_player_id'] === null) {
            throw new \RuntimeException('The active game state could not determine the current turn player.');
        }

        $playerStatement = $this->connection()->prepare(
            <<<'SQL'
            SELECT
                gp.id AS game_player_id,
                gp.display_name,
                gp.is_host,
                gp.join_status,
                gp.seat_order,
                gp.office_prestige,
                gp.permanent_coffee,
                gp.permanent_spreadsheets,
                gp.permanent_budget,
                gp.permanent_connections,
                gp.permanent_time,
                pr.coffee,
                pr.spreadsheets,
                pr.budget,
                pr.connections,
                pr.time,
                pr.executive_favor
            FROM game_players gp
            INNER JOIN player_resources pr ON pr.game_player_id = gp.id
            WHERE gp.game_id = :game_id
            ORDER BY gp.seat_order ASC
            SQL
        );
        $playerStatement->execute(['game_id' => $gameId]);
        /** @var list<array<string, mixed>> $playerRows */
        $playerRows = $playerStatement->fetchAll(PDO::FETCH_ASSOC);

        $reservedStatement = $this->connection()->prepare(
            <<<'SQL'
            SELECT
                gc.owner_game_player_id,
                c.code,
                c.tier,
                c.name,
                c.resource_discount,
                c.office_prestige,
                c.cost_coffee,
                c.cost_spreadsheets,
                c.cost_budget,
                c.cost_connections,
                c.cost_time
            FROM game_cards gc
            INNER JOIN cards c ON c.id = gc.card_id
            WHERE gc.game_id = :game_id
              AND gc.location = 'reserved'
              AND gc.owner_game_player_id IS NOT NULL
            ORDER BY gc.reserved_at ASC, gc.id ASC
            SQL
        );
        $reservedStatement->execute(['game_id' => $gameId]);
        /** @var list<array<string, mixed>> $reservedRows */
        $reservedRows = $reservedStatement->fetchAll(PDO::FETCH_ASSOC);

        /** @var array<int, list<PlayerCardView>> $reservedByPlayer */
        $reservedByPlayer = [];
        foreach ($reservedRows as $row) {
            $gamePlayerId = (int) $row['owner_game_player_id'];
            $reservedByPlayer[$gamePlayerId] ??= [];
            $reservedByPlayer[$gamePlayerId][] = new PlayerCardView(
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
            );
        }

        $purchasedStatement = $this->connection()->prepare(
            <<<'SQL'
            SELECT
                gc.owner_game_player_id,
                c.code,
                c.tier,
                c.name,
                c.resource_discount,
                c.office_prestige,
                c.cost_coffee,
                c.cost_spreadsheets,
                c.cost_budget,
                c.cost_connections,
                c.cost_time
            FROM game_cards gc
            INNER JOIN cards c ON c.id = gc.card_id
            WHERE gc.game_id = :game_id
              AND gc.location = 'purchased'
              AND gc.owner_game_player_id IS NOT NULL
            ORDER BY gc.purchased_at ASC, gc.id ASC
            SQL
        );
        $purchasedStatement->execute(['game_id' => $gameId]);
        /** @var list<array<string, mixed>> $purchasedRows */
        $purchasedRows = $purchasedStatement->fetchAll(PDO::FETCH_ASSOC);

        /** @var array<int, list<PlayerCardView>> $purchasedByPlayer */
        $purchasedByPlayer = [];
        foreach ($purchasedRows as $row) {
            $gamePlayerId = (int) $row['owner_game_player_id'];
            $purchasedByPlayer[$gamePlayerId] ??= [];
            $purchasedByPlayer[$gamePlayerId][] = new PlayerCardView(
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
            );
        }

        $claimedExecutiveStatement = $this->connection()->prepare(
            <<<'SQL'
            SELECT
                ge.owner_game_player_id,
                e.code,
                e.name,
                e.office_prestige,
                e.required_coffee,
                e.required_spreadsheets,
                e.required_budget,
                e.required_connections,
                e.required_time
            FROM game_executives ge
            INNER JOIN executives e ON e.id = ge.executive_id
            WHERE ge.game_id = :game_id
              AND ge.owner_game_player_id IS NOT NULL
            ORDER BY ge.claimed_at ASC, ge.id ASC
            SQL
        );
        $claimedExecutiveStatement->execute(['game_id' => $gameId]);
        /** @var list<array<string, mixed>> $claimedExecutiveRows */
        $claimedExecutiveRows = $claimedExecutiveStatement->fetchAll(PDO::FETCH_ASSOC);

        /** @var array<int, list<PlayerExecutiveView>> $claimedExecutivesByPlayer */
        $claimedExecutivesByPlayer = [];
        foreach ($claimedExecutiveRows as $row) {
            $gamePlayerId = (int) $row['owner_game_player_id'];
            $claimedExecutivesByPlayer[$gamePlayerId] ??= [];
            $claimedExecutivesByPlayer[$gamePlayerId][] = new PlayerExecutiveView(
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
            );
        }

        $bankStatement = $this->connection()->prepare(
            'SELECT coffee, spreadsheets, budget, connections, time, executive_favor FROM game_resource_bank WHERE game_id = :game_id LIMIT 1'
        );
        $bankStatement->execute(['game_id' => $gameId]);
        $bankRow = $bankStatement->fetch(PDO::FETCH_ASSOC);

        if ($bankRow === false) {
            throw new \RuntimeException('The active game state could not load the resource bank.');
        }

        $cardStatement = $this->connection()->prepare(
            <<<'SQL'
            SELECT
                c.code,
                c.tier,
                c.name,
                c.resource_discount,
                c.office_prestige,
                c.cost_coffee,
                c.cost_spreadsheets,
                c.cost_budget,
                c.cost_connections,
                c.cost_time,
                c.sort_order,
                gc.market_slot
            FROM game_cards gc
            INNER JOIN cards c ON c.id = gc.card_id
            WHERE gc.game_id = :game_id
              AND gc.location = 'market'
            ORDER BY c.tier ASC, gc.market_slot ASC
            SQL
        );
        $cardStatement->execute(['game_id' => $gameId]);
        /** @var list<array<string, mixed>> $cardRows */
        $cardRows = $cardStatement->fetchAll(PDO::FETCH_ASSOC);

        $executiveStatement = $this->connection()->prepare(
            <<<'SQL'
            SELECT
                e.code,
                e.name,
                e.office_prestige,
                e.required_coffee,
                e.required_spreadsheets,
                e.required_budget,
                e.required_connections,
                e.required_time,
                ge.slot_order
            FROM game_executives ge
            INNER JOIN executives e ON e.id = ge.executive_id
            WHERE ge.game_id = :game_id
              AND ge.owner_game_player_id IS NULL
            ORDER BY ge.slot_order ASC
            SQL
        );
        $executiveStatement->execute(['game_id' => $gameId]);
        /** @var list<array<string, mixed>> $executiveRows */
        $executiveRows = $executiveStatement->fetchAll(PDO::FETCH_ASSOC);

        $players = array_map(
            static fn(array $row): ActiveGamePlayer => new ActiveGamePlayer(
                gamePlayerId: (int) $row['game_player_id'],
                displayName: (string) $row['display_name'],
                isHost: (bool) $row['is_host'],
                joinStatus: (string) $row['join_status'],
                seatOrder: (int) $row['seat_order'],
                officePrestige: (int) $row['office_prestige'],
                resources: new PlayerResourceSet(
                    coffee: (int) $row['coffee'],
                    spreadsheets: (int) $row['spreadsheets'],
                    budget: (int) $row['budget'],
                    connections: (int) $row['connections'],
                    time: (int) $row['time'],
                    executiveFavor: (int) $row['executive_favor'],
                ),
                permanentDiscounts: [
                    'coffee' => (int) $row['permanent_coffee'],
                    'spreadsheets' => (int) $row['permanent_spreadsheets'],
                    'budget' => (int) $row['permanent_budget'],
                    'connections' => (int) $row['permanent_connections'],
                    'time' => (int) $row['permanent_time'],
                ],
                reservedCards: $reservedByPlayer[(int) $row['game_player_id']] ?? [],
                purchasedCards: $purchasedByPlayer[(int) $row['game_player_id']] ?? [],
                claimedExecutives: $claimedExecutivesByPlayer[(int) $row['game_player_id']] ?? [],
            ),
            $playerRows,
        );

        $marketCardsByTier = [1 => [], 2 => [], 3 => []];
        foreach ($cardRows as $row) {
            $marketCardsByTier[(int) $row['tier']][] = new CardSeedDefinition(
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
            );
        }

        $executives = array_map(
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
            $executiveRows,
        );

        return new ActiveGameState(
            players: $players,
            currentTurnGamePlayerId: (int) $gameRow['current_turn_game_player_id'],
            bank: [
                'coffee' => (int) $bankRow['coffee'],
                'spreadsheets' => (int) $bankRow['spreadsheets'],
                'budget' => (int) $bankRow['budget'],
                'connections' => (int) $bankRow['connections'],
                'time' => (int) $bankRow['time'],
                'executiveFavor' => (int) $bankRow['executive_favor'],
            ],
            marketCardsByTier: $marketCardsByTier,
            executives: $executives,
        );
    }

    public function applyTakeResources(
        int $gameId,
        int $actingGamePlayerId,
        array $resources,
        ActiveGameState $state,
    ): ActiveGameState {
        $connection = $this->connection();
        $connection->beginTransaction();
        $currentPhase = $this->currentGamePhase($connection, $gameId);

        try {
            $resourceCounts = array_count_values($resources);

            foreach ($resourceCounts as $resource => $count) {
                $column = $this->resourceColumn($resource);

                $connection->prepare(
                    sprintf('UPDATE player_resources SET %1$s = %1$s + :count WHERE game_player_id = :game_player_id', $column)
                )->execute([
                    'count' => $count,
                    'game_player_id' => $actingGamePlayerId,
                ]);

                $connection->prepare(
                    sprintf('UPDATE game_resource_bank SET %1$s = %1$s - :count WHERE game_id = :game_id', $column)
                )->execute([
                    'count' => $count,
                    'game_id' => $gameId,
                ]);
            }

            $nextPlayerId = $this->nextPlayerId($state, $actingGamePlayerId);

            $turnStatement = $connection->prepare('SELECT COALESCE(MAX(turn_number), 0) FROM game_turns WHERE game_id = :game_id');
            $turnStatement->execute(['game_id' => $gameId]);
            $turnNumber = ((int) $turnStatement->fetchColumn()) + 1;
            $roundNumber = intdiv($turnNumber - 1, max(count($state->players), 1)) + 1;

            $connection->prepare(
                <<<'SQL'
                INSERT INTO game_turns (game_id, round_number, turn_number, game_player_id, action_type, action_payload, was_legal, resolved_at)
                VALUES (:game_id, :round_number, :turn_number, :game_player_id, :action_type, :action_payload, 1, CURRENT_TIMESTAMP)
                SQL
            )->execute([
                'game_id' => $gameId,
                'round_number' => $roundNumber,
                'turn_number' => $turnNumber,
                'game_player_id' => $actingGamePlayerId,
                'action_type' => 'take_resources',
                'action_payload' => json_encode(['resources' => $resources], JSON_UNESCAPED_SLASHES),
            ]);

            $connection->prepare(
                'UPDATE games SET current_turn_game_player_id = :current_turn_game_player_id WHERE id = :game_id'
            )->execute([
                'current_turn_game_player_id' => $nextPlayerId,
                'game_id' => $gameId,
            ]);

            $updatedState = $this->loadState($gameId);
            $this->applyPostTurnGameProgression($connection, $gameId, $actingGamePlayerId, $updatedState, $currentPhase);

            $connection->prepare(
                <<<'SQL'
                INSERT INTO game_events (game_id, event_type, actor_game_player_id, payload)
                VALUES (:game_id, :event_type, :actor_game_player_id, :payload)
                SQL
            )->execute([
                'game_id' => $gameId,
                'event_type' => 'resources_taken',
                'actor_game_player_id' => $actingGamePlayerId,
                'payload' => json_encode([
                    'resources' => $resources,
                    'state' => $updatedState->toArray(),
                ], JSON_UNESCAPED_SLASHES),
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
                'snapshot_json' => json_encode($updatedState->toArray(), JSON_UNESCAPED_SLASHES),
            ]);

            $connection->commit();

            return $updatedState;
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    public function applyClaimProject(
        int $gameId,
        int $actingGamePlayerId,
        string $source,
        int $tier,
        ?int $marketSlot,
        ActiveGameState $state,
    ): ActiveGameState {
        $connection = $this->connection();
        $connection->beginTransaction();
        $currentPhase = $this->currentGamePhase($connection, $gameId);

        try {
            if ($source === 'market') {
                $statement = $connection->prepare(
                    <<<'SQL'
                    SELECT id
                    FROM game_cards
                    WHERE game_id = :game_id
                      AND location = 'market'
                      AND tier = :tier
                      AND market_slot = :market_slot
                    LIMIT 1
                    SQL
                );
                $statement->execute([
                    'game_id' => $gameId,
                    'tier' => $tier,
                    'market_slot' => $marketSlot,
                ]);
                $gameCardId = $statement->fetchColumn();

                if ($gameCardId === false) {
                    throw new \RuntimeException('The requested market card could not be found.');
                }

                $connection->prepare(
                    <<<'SQL'
                    UPDATE game_cards
                    SET location = 'reserved',
                        owner_game_player_id = :owner_game_player_id,
                        market_slot = NULL,
                        reserved_at = CURRENT_TIMESTAMP
                    WHERE id = :game_card_id
                    SQL
                )->execute([
                    'owner_game_player_id' => $actingGamePlayerId,
                    'game_card_id' => $gameCardId,
                ]);

                $deckStatement = $connection->prepare(
                    <<<'SQL'
                    SELECT id
                    FROM game_cards
                    WHERE game_id = :game_id
                      AND location = 'deck'
                      AND tier = :tier
                    ORDER BY deck_position ASC
                    LIMIT 1
                    SQL
                );
                $deckStatement->execute([
                    'game_id' => $gameId,
                    'tier' => $tier,
                ]);
                $replacementId = $deckStatement->fetchColumn();

                if ($replacementId !== false) {
                    $connection->prepare(
                        <<<'SQL'
                        UPDATE game_cards
                        SET location = 'market',
                            deck_position = NULL,
                            market_slot = :market_slot
                        WHERE id = :game_card_id
                        SQL
                    )->execute([
                        'market_slot' => $marketSlot,
                        'game_card_id' => $replacementId,
                    ]);
                }
            } else {
                $statement = $connection->prepare(
                    <<<'SQL'
                    SELECT id
                    FROM game_cards
                    WHERE game_id = :game_id
                      AND location = 'deck'
                      AND tier = :tier
                    ORDER BY deck_position ASC
                    LIMIT 1
                    SQL
                );
                $statement->execute([
                    'game_id' => $gameId,
                    'tier' => $tier,
                ]);
                $gameCardId = $statement->fetchColumn();

                if ($gameCardId === false) {
                    throw new \RuntimeException('The requested deck card could not be found.');
                }

                $connection->prepare(
                    <<<'SQL'
                    UPDATE game_cards
                    SET location = 'reserved',
                        owner_game_player_id = :owner_game_player_id,
                        deck_position = NULL,
                        reserved_at = CURRENT_TIMESTAMP
                    WHERE id = :game_card_id
                    SQL
                )->execute([
                    'owner_game_player_id' => $actingGamePlayerId,
                    'game_card_id' => $gameCardId,
                ]);
            }

            $actingPlayer = $state->playerById($actingGamePlayerId)
                ?? throw new \RuntimeException('The acting player could not be found in the active game state.');

            $gainedExecutiveFavor = ($state->bank['executiveFavor'] ?? 0) > 0;
            if ($gainedExecutiveFavor) {
                $connection->prepare(
                    'UPDATE player_resources SET executive_favor = executive_favor + 1 WHERE game_player_id = :game_player_id'
                )->execute([
                    'game_player_id' => $actingGamePlayerId,
                ]);
                $connection->prepare(
                    'UPDATE game_resource_bank SET executive_favor = executive_favor - 1 WHERE game_id = :game_id'
                )->execute([
                    'game_id' => $gameId,
                ]);
            }

            $nextPlayerId = $this->nextPlayerId($state, $actingGamePlayerId);

            $turnStatement = $connection->prepare('SELECT COALESCE(MAX(turn_number), 0) FROM game_turns WHERE game_id = :game_id');
            $turnStatement->execute(['game_id' => $gameId]);
            $turnNumber = ((int) $turnStatement->fetchColumn()) + 1;
            $roundNumber = intdiv($turnNumber - 1, max(count($state->players), 1)) + 1;

            $connection->prepare(
                <<<'SQL'
                INSERT INTO game_turns (game_id, round_number, turn_number, game_player_id, action_type, action_payload, was_legal, resolved_at)
                VALUES (:game_id, :round_number, :turn_number, :game_player_id, :action_type, :action_payload, 1, CURRENT_TIMESTAMP)
                SQL
            )->execute([
                'game_id' => $gameId,
                'round_number' => $roundNumber,
                'turn_number' => $turnNumber,
                'game_player_id' => $actingGamePlayerId,
                'action_type' => 'claim_project',
                'action_payload' => json_encode([
                    'source' => $source,
                    'tier' => $tier,
                    'marketSlot' => $marketSlot,
                    'gainedExecutiveFavor' => $gainedExecutiveFavor,
                ], JSON_UNESCAPED_SLASHES),
            ]);

            $connection->prepare(
                'UPDATE games SET current_turn_game_player_id = :current_turn_game_player_id WHERE id = :game_id'
            )->execute([
                'current_turn_game_player_id' => $nextPlayerId,
                'game_id' => $gameId,
            ]);

            $updatedState = $this->loadState($gameId);
            $this->applyPostTurnGameProgression($connection, $gameId, $actingGamePlayerId, $updatedState, $currentPhase);

            $connection->prepare(
                <<<'SQL'
                INSERT INTO game_events (game_id, event_type, actor_game_player_id, payload)
                VALUES (:game_id, :event_type, :actor_game_player_id, :payload)
                SQL
            )->execute([
                'game_id' => $gameId,
                'event_type' => 'project_claimed',
                'actor_game_player_id' => $actingGamePlayerId,
                'payload' => json_encode([
                    'source' => $source,
                    'tier' => $tier,
                    'marketSlot' => $marketSlot,
                    'gainedExecutiveFavor' => $gainedExecutiveFavor,
                    'state' => $updatedState->toArray(),
                ], JSON_UNESCAPED_SLASHES),
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
                'snapshot_json' => json_encode($updatedState->toArray(), JSON_UNESCAPED_SLASHES),
            ]);

            $connection->commit();

            return $updatedState;
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    public function applyPurchaseAdvantage(
        int $gameId,
        int $actingGamePlayerId,
        string $source,
        ?int $tier,
        ?int $marketSlot,
        ?string $cardCode,
        array $spentResources,
        ActiveGameState $state,
    ): ActiveGameState {
        $connection = $this->connection();
        $connection->beginTransaction();
        $currentPhase = $this->currentGamePhase($connection, $gameId);

        try {
            $selectedCardRow = null;
            $actingPlayer = $state->playerById($actingGamePlayerId)
                ?? throw new \RuntimeException('The acting player could not be found in the active game state.');

            if ($source === 'market') {
                $statement = $connection->prepare(
                    <<<'SQL'
                    SELECT gc.id, gc.market_slot, c.resource_discount, c.office_prestige
                    FROM game_cards gc
                    INNER JOIN cards c ON c.id = gc.card_id
                    WHERE gc.game_id = :game_id
                      AND gc.location = 'market'
                      AND gc.tier = :tier
                      AND gc.market_slot = :market_slot
                    LIMIT 1
                    SQL
                );
                $statement->execute([
                    'game_id' => $gameId,
                    'tier' => $tier,
                    'market_slot' => $marketSlot,
                ]);
                $selectedCardRow = $statement->fetch(PDO::FETCH_ASSOC);

                if ($selectedCardRow === false) {
                    throw new \RuntimeException('The selected market card could not be found.');
                }

                $connection->prepare(
                    <<<'SQL'
                    UPDATE game_cards
                    SET location = 'purchased',
                        owner_game_player_id = :owner_game_player_id,
                        market_slot = NULL,
                        purchased_at = CURRENT_TIMESTAMP
                    WHERE id = :game_card_id
                    SQL
                )->execute([
                    'owner_game_player_id' => $actingGamePlayerId,
                    'game_card_id' => $selectedCardRow['id'],
                ]);

                $deckStatement = $connection->prepare(
                    <<<'SQL'
                    SELECT id
                    FROM game_cards
                    WHERE game_id = :game_id
                      AND location = 'deck'
                      AND tier = :tier
                    ORDER BY deck_position ASC
                    LIMIT 1
                    SQL
                );
                $deckStatement->execute([
                    'game_id' => $gameId,
                    'tier' => $tier,
                ]);
                $replacementId = $deckStatement->fetchColumn();

                if ($replacementId !== false) {
                    $connection->prepare(
                        <<<'SQL'
                        UPDATE game_cards
                        SET location = 'market',
                            market_slot = :market_slot,
                            deck_position = NULL
                        WHERE id = :game_card_id
                        SQL
                    )->execute([
                        'market_slot' => $marketSlot,
                        'game_card_id' => $replacementId,
                    ]);
                }
            } else {
                $statement = $connection->prepare(
                    <<<'SQL'
                    SELECT gc.id, c.resource_discount, c.office_prestige
                    FROM game_cards gc
                    INNER JOIN cards c ON c.id = gc.card_id
                    WHERE gc.game_id = :game_id
                      AND gc.location = 'reserved'
                      AND gc.owner_game_player_id = :owner_game_player_id
                      AND c.code = :card_code
                    LIMIT 1
                    SQL
                );
                $statement->execute([
                    'game_id' => $gameId,
                    'owner_game_player_id' => $actingGamePlayerId,
                    'card_code' => $cardCode,
                ]);
                $selectedCardRow = $statement->fetch(PDO::FETCH_ASSOC);

                if ($selectedCardRow === false) {
                    throw new \RuntimeException('The selected reserved card could not be found.');
                }

                $connection->prepare(
                    <<<'SQL'
                    UPDATE game_cards
                    SET location = 'purchased',
                        purchased_at = CURRENT_TIMESTAMP
                    WHERE id = :game_card_id
                    SQL
                )->execute([
                    'game_card_id' => $selectedCardRow['id'],
                ]);
            }

            $updatedDiscounts = [
                ...$actingPlayer->permanentDiscounts,
                (string) $selectedCardRow['resource_discount'] => $actingPlayer->permanentDiscounts[(string) $selectedCardRow['resource_discount']] + 1,
            ];
            $awardedExecutiveRow = $this->findAwardableExecutiveRow($connection, $gameId, $updatedDiscounts);

            foreach ($spentResources as $resource => $count) {
                if ($count <= 0) {
                    continue;
                }

                $column = $this->resourceColumn($resource);

                $connection->prepare(
                    sprintf('UPDATE player_resources SET %1$s = %1$s - :count WHERE game_player_id = :game_player_id', $column)
                )->execute([
                    'count' => $count,
                    'game_player_id' => $actingGamePlayerId,
                ]);

                $connection->prepare(
                    sprintf('UPDATE game_resource_bank SET %1$s = %1$s + :count WHERE game_id = :game_id', $column)
                )->execute([
                    'count' => $count,
                    'game_id' => $gameId,
                ]);
            }

            $discountColumn = $this->permanentDiscountColumn((string) $selectedCardRow['resource_discount']);
            $connection->prepare(
                sprintf(
                    'UPDATE game_players SET %1$s = %1$s + 1, office_prestige = office_prestige + :office_prestige WHERE id = :game_player_id',
                    $discountColumn,
                )
            )->execute([
                'office_prestige' => (int) $selectedCardRow['office_prestige'] + ((int) ($awardedExecutiveRow['office_prestige'] ?? 0)),
                'game_player_id' => $actingGamePlayerId,
            ]);

            if ($awardedExecutiveRow !== null) {
                $connection->prepare(
                    <<<'SQL'
                    UPDATE game_executives
                    SET owner_game_player_id = :owner_game_player_id,
                        claimed_at = CURRENT_TIMESTAMP
                    WHERE id = :game_executive_id
                    SQL
                )->execute([
                    'owner_game_player_id' => $actingGamePlayerId,
                    'game_executive_id' => $awardedExecutiveRow['id'],
                ]);
            }

            $nextPlayerId = $this->nextPlayerId($state, $actingGamePlayerId);

            $turnStatement = $connection->prepare('SELECT COALESCE(MAX(turn_number), 0) FROM game_turns WHERE game_id = :game_id');
            $turnStatement->execute(['game_id' => $gameId]);
            $turnNumber = ((int) $turnStatement->fetchColumn()) + 1;
            $roundNumber = intdiv($turnNumber - 1, max(count($state->players), 1)) + 1;

            $connection->prepare(
                <<<'SQL'
                INSERT INTO game_turns (game_id, round_number, turn_number, game_player_id, action_type, action_payload, was_legal, resolved_at)
                VALUES (:game_id, :round_number, :turn_number, :game_player_id, :action_type, :action_payload, 1, CURRENT_TIMESTAMP)
                SQL
            )->execute([
                'game_id' => $gameId,
                'round_number' => $roundNumber,
                'turn_number' => $turnNumber,
                'game_player_id' => $actingGamePlayerId,
                'action_type' => 'purchase_advantage',
                'action_payload' => json_encode([
                    'source' => $source,
                    'tier' => $tier,
                    'marketSlot' => $marketSlot,
                    'cardCode' => $cardCode,
                    'spentResources' => $spentResources,
                    'awardedExecutiveCode' => $awardedExecutiveRow['code'] ?? null,
                ], JSON_UNESCAPED_SLASHES),
            ]);

            $connection->prepare(
                'UPDATE games SET current_turn_game_player_id = :current_turn_game_player_id WHERE id = :game_id'
            )->execute([
                'current_turn_game_player_id' => $nextPlayerId,
                'game_id' => $gameId,
            ]);

            $updatedState = $this->loadState($gameId);
            $this->applyPostTurnGameProgression($connection, $gameId, $actingGamePlayerId, $updatedState, $currentPhase);

            $connection->prepare(
                <<<'SQL'
                INSERT INTO game_events (game_id, event_type, actor_game_player_id, payload)
                VALUES (:game_id, :event_type, :actor_game_player_id, :payload)
                SQL
            )->execute([
                'game_id' => $gameId,
                'event_type' => 'advantage_purchased',
                'actor_game_player_id' => $actingGamePlayerId,
                'payload' => json_encode([
                    'source' => $source,
                    'tier' => $tier,
                    'marketSlot' => $marketSlot,
                    'cardCode' => $cardCode,
                    'spentResources' => $spentResources,
                    'awardedExecutiveCode' => $awardedExecutiveRow['code'] ?? null,
                    'state' => $updatedState->toArray(),
                ], JSON_UNESCAPED_SLASHES),
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
                'snapshot_json' => json_encode($updatedState->toArray(), JSON_UNESCAPED_SLASHES),
            ]);

            $connection->commit();

            return $updatedState;
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    private function nextPlayerId(ActiveGameState $state, int $actingGamePlayerId): int
    {
        $players = $state->players;
        usort(
            $players,
            static fn(ActiveGamePlayer $left, ActiveGamePlayer $right): int => $left->seatOrder <=> $right->seatOrder,
        );

        foreach ($players as $index => $player) {
            if ($player->gamePlayerId === $actingGamePlayerId) {
                return $players[($index + 1) % count($players)]->gamePlayerId;
            }
        }

        throw new \RuntimeException('Could not determine the next player turn from the active game state.');
    }

    private function resourceColumn(string $resource): string
    {
        return match ($resource) {
            'coffee', 'spreadsheets', 'budget', 'connections', 'time' => $resource,
            'executiveFavor' => 'executive_favor',
            default => throw new \InvalidArgumentException('Unsupported resource column requested.'),
        };
    }

    private function permanentDiscountColumn(string $resource): string
    {
        return match ($resource) {
            'coffee' => 'permanent_coffee',
            'spreadsheets' => 'permanent_spreadsheets',
            'budget' => 'permanent_budget',
            'connections' => 'permanent_connections',
            'time' => 'permanent_time',
            default => throw new \InvalidArgumentException('Unsupported permanent discount column requested.'),
        };
    }

    private function currentGamePhase(PDO $connection, int $gameId): string
    {
        $statement = $connection->prepare('SELECT phase FROM games WHERE id = :game_id LIMIT 1');
        $statement->execute(['game_id' => $gameId]);
        $phase = $statement->fetchColumn();

        if (!is_string($phase) || $phase === '') {
            throw new \RuntimeException('The current game phase could not be determined.');
        }

        return $phase;
    }

    private function applyPostTurnGameProgression(
        PDO $connection,
        int $gameId,
        int $actingGamePlayerId,
        ActiveGameState $updatedState,
        string $currentPhase,
    ): void {
        $shouldTriggerEndgame = $currentPhase === 'active'
            && $this->endgameResolver->shouldTriggerEndgame($updatedState, $actingGamePlayerId);

        if ($shouldTriggerEndgame && !$this->endgameResolver->isLastSeat($updatedState, $actingGamePlayerId)) {
            $connection->prepare(
                <<<'SQL'
                UPDATE games
                SET phase = 'endgame',
                    endgame_triggered_by_game_player_id = :acting_game_player_id
                WHERE id = :game_id
                SQL
            )->execute([
                'acting_game_player_id' => $actingGamePlayerId,
                'game_id' => $gameId,
            ]);

            return;
        }

        $shouldCompleteGame = ($currentPhase === 'endgame' || $shouldTriggerEndgame)
            && $this->endgameResolver->isLastSeat($updatedState, $actingGamePlayerId);

        if (!$shouldCompleteGame) {
            return;
        }

        $winner = $this->endgameResolver->resolveWinner($updatedState);
        $connection->prepare(
            <<<'SQL'
            UPDATE games
            SET status = 'completed',
                phase = 'completed',
                winning_game_player_id = :winning_game_player_id,
                endgame_triggered_by_game_player_id = COALESCE(endgame_triggered_by_game_player_id, :acting_game_player_id),
                ended_at = CURRENT_TIMESTAMP
            WHERE id = :game_id
            SQL
        )->execute([
            'winning_game_player_id' => $winner->winnerGamePlayerId,
            'acting_game_player_id' => $actingGamePlayerId,
            'game_id' => $gameId,
        ]);
    }

    /**
     * @param array<string, int> $permanentDiscounts
     * @return array<string, mixed>|null
     */
    private function findAwardableExecutiveRow(PDO $connection, int $gameId, array $permanentDiscounts): ?array
    {
        $statement = $connection->prepare(
            <<<'SQL'
            SELECT
                ge.id,
                e.code,
                e.office_prestige,
                e.required_coffee,
                e.required_spreadsheets,
                e.required_budget,
                e.required_connections,
                e.required_time
            FROM game_executives ge
            INNER JOIN executives e ON e.id = ge.executive_id
            WHERE ge.game_id = :game_id
              AND ge.owner_game_player_id IS NULL
            ORDER BY ge.slot_order ASC
            SQL
        );
        $statement->execute(['game_id' => $gameId]);
        /** @var list<array<string, mixed>> $rows */
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            if (
                $permanentDiscounts['coffee'] >= (int) $row['required_coffee']
                && $permanentDiscounts['spreadsheets'] >= (int) $row['required_spreadsheets']
                && $permanentDiscounts['budget'] >= (int) $row['required_budget']
                && $permanentDiscounts['connections'] >= (int) $row['required_connections']
                && $permanentDiscounts['time'] >= (int) $row['required_time']
            ) {
                return $row;
            }
        }

        return null;
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
