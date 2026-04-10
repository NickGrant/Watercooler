<?php

declare(strict_types=1);

namespace Watercooler\Api\Database;

use PDO;
use RuntimeException;
use Watercooler\Api\Games\ActiveGamePlayer;
use Watercooler\Api\Games\ActiveGameState;
use Watercooler\Api\Games\CardSeedDefinition;
use Watercooler\Api\Games\ExecutiveSeedDefinition;
use Watercooler\Api\Games\PlayerCardView;
use Watercooler\Api\Games\PlayerExecutiveView;
use Watercooler\Api\Games\PlayerResourceSet;

final class PdoActiveGameStateLoader
{
    public function load(PDO $connection, int $gameId): ActiveGameState
    {
        $gameStatement = $connection->prepare(
            'SELECT current_turn_game_player_id FROM games WHERE id = :game_id LIMIT 1'
        );
        $gameStatement->execute(['game_id' => $gameId]);
        $gameRow = $gameStatement->fetch(PDO::FETCH_ASSOC);

        if ($gameRow === false || $gameRow['current_turn_game_player_id'] === null) {
            throw new RuntimeException('The active game state could not determine the current turn player.');
        }

        $playerStatement = $connection->prepare(
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

        $reservedByPlayer = $this->loadPlayerCardsByLocation($connection, $gameId, 'reserved', 'gc.reserved_at ASC, gc.id ASC');
        $purchasedByPlayer = $this->loadPlayerCardsByLocation($connection, $gameId, 'purchased', 'gc.purchased_at ASC, gc.id ASC');
        $claimedExecutivesByPlayer = $this->loadClaimedExecutivesByPlayer($connection, $gameId);

        $bankStatement = $connection->prepare(
            'SELECT coffee, spreadsheets, budget, connections, time, executive_favor FROM game_resource_bank WHERE game_id = :game_id LIMIT 1'
        );
        $bankStatement->execute(['game_id' => $gameId]);
        $bankRow = $bankStatement->fetch(PDO::FETCH_ASSOC);

        if ($bankRow === false) {
            throw new RuntimeException('The active game state could not load the resource bank.');
        }

        $cardStatement = $connection->prepare(
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

        $executiveStatement = $connection->prepare(
            <<<'SQL'
            SELECT
                e.code,
                e.name,
                e.portrait_asset,
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
            function (array $row) use ($reservedByPlayer, $purchasedByPlayer, $claimedExecutivesByPlayer): ActiveGamePlayer {
                $gamePlayerId = (int) $row['game_player_id'];

                return new ActiveGamePlayer(
                    gamePlayerId: $gamePlayerId,
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
                    reservedCards: $reservedByPlayer[$gamePlayerId] ?? [],
                    purchasedCards: $purchasedByPlayer[$gamePlayerId] ?? [],
                    claimedExecutives: $claimedExecutivesByPlayer[$gamePlayerId] ?? [],
                );
            },
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
                portraitAsset: isset($row['portrait_asset']) ? (string) $row['portrait_asset'] : null,
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

    /**
     * @return array<int, list<PlayerCardView>>
     */
    private function loadPlayerCardsByLocation(
        PDO $connection,
        int $gameId,
        string $location,
        string $orderBy
    ): array {
        $statement = $connection->prepare(
            <<<SQL
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
              AND gc.location = :location
              AND gc.owner_game_player_id IS NOT NULL
            ORDER BY {$orderBy}
            SQL
        );
        $statement->execute([
            'game_id' => $gameId,
            'location' => $location,
        ]);
        /** @var list<array<string, mixed>> $rows */
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $cardsByPlayer = [];
        foreach ($rows as $row) {
            $gamePlayerId = (int) $row['owner_game_player_id'];
            $cardsByPlayer[$gamePlayerId] ??= [];
            $cardsByPlayer[$gamePlayerId][] = new PlayerCardView(
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

        return $cardsByPlayer;
    }

    /**
     * @return array<int, list<PlayerExecutiveView>>
     */
    private function loadClaimedExecutivesByPlayer(PDO $connection, int $gameId): array
    {
        $claimedExecutiveStatement = $connection->prepare(
            <<<'SQL'
            SELECT
                ge.owner_game_player_id,
                e.code,
                e.name,
                e.portrait_asset,
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

        return $claimedExecutivesByPlayer;
    }
}
