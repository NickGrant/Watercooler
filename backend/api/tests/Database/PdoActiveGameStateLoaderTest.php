<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Database;

use PDO;
use PHPUnit\Framework\TestCase;
use Watercooler\Api\Database\PdoActiveGameStateLoader;

final class PdoActiveGameStateLoaderTest extends TestCase
{
    public function testItBuildsTheActiveGameStateProjectionFromDatabaseRows(): void
    {
        $connection = new PDO('sqlite::memory:');
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createSchema($connection);
        $this->seedState($connection);

        $loader = new PdoActiveGameStateLoader();
        $state = $loader->load($connection, 1);

        self::assertSame(2, $state->currentTurnGamePlayerId);
        self::assertCount(2, $state->players);
        self::assertSame('Pam', $state->players[0]->displayName);
        self::assertSame(2, $state->players[0]->reservedCards[0]->tier);
        self::assertSame('Reserved Initiative', $state->players[0]->reservedCards[0]->name);
        self::assertSame('Purchased Workflow', $state->players[0]->purchasedCards[0]->name);
        self::assertSame('VP of Synergy', $state->players[0]->claimedExecutives[0]->name);
        self::assertSame(4, $state->bank['coffee']);
        self::assertSame('Budget Buffer', $state->marketCardsByTier[1][0]->name);
        self::assertSame('Director of Roadmaps', $state->executives[0]->name);
    }

    private function createSchema(PDO $connection): void
    {
        $connection->exec(
            <<<'SQL'
            CREATE TABLE games (
                id INTEGER PRIMARY KEY,
                current_turn_game_player_id INTEGER NOT NULL
            );

            CREATE TABLE game_players (
                id INTEGER PRIMARY KEY,
                game_id INTEGER NOT NULL,
                display_name TEXT NOT NULL,
                is_host INTEGER NOT NULL,
                join_status TEXT NOT NULL,
                seat_order INTEGER NOT NULL,
                office_prestige INTEGER NOT NULL,
                permanent_coffee INTEGER NOT NULL,
                permanent_spreadsheets INTEGER NOT NULL,
                permanent_budget INTEGER NOT NULL,
                permanent_connections INTEGER NOT NULL,
                permanent_time INTEGER NOT NULL
            );

            CREATE TABLE player_resources (
                game_player_id INTEGER PRIMARY KEY,
                coffee INTEGER NOT NULL,
                spreadsheets INTEGER NOT NULL,
                budget INTEGER NOT NULL,
                connections INTEGER NOT NULL,
                time INTEGER NOT NULL,
                executive_favor INTEGER NOT NULL
            );

            CREATE TABLE cards (
                id INTEGER PRIMARY KEY,
                code TEXT NOT NULL,
                tier INTEGER NOT NULL,
                name TEXT NOT NULL,
                resource_discount TEXT NOT NULL,
                office_prestige INTEGER NOT NULL,
                cost_coffee INTEGER NOT NULL,
                cost_spreadsheets INTEGER NOT NULL,
                cost_budget INTEGER NOT NULL,
                cost_connections INTEGER NOT NULL,
                cost_time INTEGER NOT NULL,
                sort_order INTEGER NOT NULL
            );

            CREATE TABLE game_cards (
                id INTEGER PRIMARY KEY,
                game_id INTEGER NOT NULL,
                card_id INTEGER NOT NULL,
                tier INTEGER NOT NULL,
                location TEXT NOT NULL,
                owner_game_player_id INTEGER NULL,
                market_slot INTEGER NULL,
                reserved_at TEXT NULL,
                purchased_at TEXT NULL
            );

            CREATE TABLE executives (
                id INTEGER PRIMARY KEY,
                code TEXT NOT NULL,
                name TEXT NOT NULL,
                portrait_asset TEXT NULL,
                office_prestige INTEGER NOT NULL,
                required_coffee INTEGER NOT NULL,
                required_spreadsheets INTEGER NOT NULL,
                required_budget INTEGER NOT NULL,
                required_connections INTEGER NOT NULL,
                required_time INTEGER NOT NULL
            );

            CREATE TABLE game_executives (
                id INTEGER PRIMARY KEY,
                game_id INTEGER NOT NULL,
                executive_id INTEGER NOT NULL,
                owner_game_player_id INTEGER NULL,
                slot_order INTEGER NOT NULL,
                claimed_at TEXT NULL
            );

            CREATE TABLE game_resource_bank (
                game_id INTEGER PRIMARY KEY,
                coffee INTEGER NOT NULL,
                spreadsheets INTEGER NOT NULL,
                budget INTEGER NOT NULL,
                connections INTEGER NOT NULL,
                time INTEGER NOT NULL,
                executive_favor INTEGER NOT NULL
            );
            SQL
        );
    }

    private function seedState(PDO $connection): void
    {
        $connection->exec(
            <<<'SQL'
            INSERT INTO games (id, current_turn_game_player_id) VALUES (1, 2);

            INSERT INTO game_players (
                id, game_id, display_name, is_host, join_status, seat_order, office_prestige,
                permanent_coffee, permanent_spreadsheets, permanent_budget, permanent_connections, permanent_time
            ) VALUES
                (1, 1, 'Pam', 1, 'connected', 1, 5, 1, 0, 1, 0, 0),
                (2, 1, 'Jim', 0, 'connected', 2, 3, 0, 1, 0, 0, 0);

            INSERT INTO player_resources (game_player_id, coffee, spreadsheets, budget, connections, time, executive_favor) VALUES
                (1, 1, 2, 0, 1, 0, 1),
                (2, 0, 1, 1, 0, 1, 0);

            INSERT INTO cards (
                id, code, tier, name, resource_discount, office_prestige,
                cost_coffee, cost_spreadsheets, cost_budget, cost_connections, cost_time, sort_order
            ) VALUES
                (1, 'market-1', 1, 'Budget Buffer', 'coffee', 1, 2, 0, 1, 0, 0, 1),
                (2, 'reserved-1', 2, 'Reserved Initiative', 'budget', 2, 0, 2, 2, 0, 1, 2),
                (3, 'purchased-1', 1, 'Purchased Workflow', 'spreadsheets', 1, 0, 1, 1, 0, 0, 3);

            INSERT INTO game_cards (
                id, game_id, card_id, tier, location, owner_game_player_id, market_slot, reserved_at, purchased_at
            ) VALUES
                (1, 1, 1, 1, 'market', NULL, 1, NULL, NULL),
                (2, 1, 2, 2, 'reserved', 1, NULL, '2026-04-10 10:00:00', NULL),
                (3, 1, 3, 1, 'purchased', 1, NULL, NULL, '2026-04-10 10:05:00');

            INSERT INTO executives (
                id, code, name, portrait_asset, office_prestige,
                required_coffee, required_spreadsheets, required_budget, required_connections, required_time
            ) VALUES
                (1, 'vp-synergy', 'VP of Synergy', 'executive-1.png', 3, 3, 0, 3, 3, 0),
                (2, 'director-roadmaps', 'Director of Roadmaps', 'executive-2.png', 3, 0, 3, 3, 0, 3);

            INSERT INTO game_executives (id, game_id, executive_id, owner_game_player_id, slot_order, claimed_at) VALUES
                (1, 1, 1, 1, 1, '2026-04-10 10:10:00'),
                (2, 1, 2, NULL, 1, NULL);

            INSERT INTO game_resource_bank (game_id, coffee, spreadsheets, budget, connections, time, executive_favor) VALUES
                (1, 4, 3, 4, 4, 5, 5);
            SQL
        );
    }
}
