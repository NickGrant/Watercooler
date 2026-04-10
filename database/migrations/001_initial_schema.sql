CREATE TABLE IF NOT EXISTS schema_migrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(64) NOT NULL,
    description VARCHAR(255) NOT NULL,
    executed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_schema_migrations_version (version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS games (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(80) NOT NULL,
    status ENUM('lobby', 'active', 'completed', 'abandoned') NOT NULL DEFAULT 'lobby',
    phase ENUM('pre_join', 'lobby', 'active', 'endgame', 'completed') NOT NULL DEFAULT 'pre_join',
    host_game_player_id BIGINT UNSIGNED NULL,
    current_turn_game_player_id BIGINT UNSIGNED NULL,
    winning_game_player_id BIGINT UNSIGNED NULL,
    endgame_triggered_by_game_player_id BIGINT UNSIGNED NULL,
    started_at TIMESTAMP NULL DEFAULT NULL,
    ended_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_games_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS players (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS player_avatars (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    player_id BIGINT UNSIGNED NOT NULL,
    body_option VARCHAR(64) NOT NULL,
    face_option VARCHAR(64) NOT NULL,
    hair_option VARCHAR(64) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_player_avatars_player_id (player_id),
    CONSTRAINT fk_player_avatars_player
        FOREIGN KEY (player_id) REFERENCES players (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_players (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id BIGINT UNSIGNED NOT NULL,
    player_id BIGINT UNSIGNED NOT NULL,
    display_name VARCHAR(64) NOT NULL,
    seat_order TINYINT UNSIGNED NULL,
    is_host TINYINT(1) NOT NULL DEFAULT 0,
    join_status ENUM('joined', 'connected', 'disconnected', 'left') NOT NULL DEFAULT 'joined',
    session_token_hash CHAR(64) NULL,
    office_prestige SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    permanent_coffee TINYINT UNSIGNED NOT NULL DEFAULT 0,
    permanent_spreadsheets TINYINT UNSIGNED NOT NULL DEFAULT 0,
    permanent_budget TINYINT UNSIGNED NOT NULL DEFAULT 0,
    permanent_connections TINYINT UNSIGNED NOT NULL DEFAULT 0,
    permanent_time TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_game_players_game_player (game_id, player_id),
    UNIQUE KEY uq_game_players_game_display_name (game_id, display_name),
    UNIQUE KEY uq_game_players_session_token_hash (session_token_hash),
    CONSTRAINT fk_game_players_game
        FOREIGN KEY (game_id) REFERENCES games (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_game_players_player
        FOREIGN KEY (player_id) REFERENCES players (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE games
    ADD CONSTRAINT fk_games_host_game_player
        FOREIGN KEY (host_game_player_id) REFERENCES game_players (id)
        ON DELETE SET NULL,
    ADD CONSTRAINT fk_games_current_turn_game_player
        FOREIGN KEY (current_turn_game_player_id) REFERENCES game_players (id)
        ON DELETE SET NULL,
    ADD CONSTRAINT fk_games_winning_game_player
        FOREIGN KEY (winning_game_player_id) REFERENCES game_players (id)
        ON DELETE SET NULL,
    ADD CONSTRAINT fk_games_endgame_triggered_by_game_player
        FOREIGN KEY (endgame_triggered_by_game_player_id) REFERENCES game_players (id)
        ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS cards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(64) NOT NULL,
    tier TINYINT UNSIGNED NOT NULL,
    name VARCHAR(128) NOT NULL,
    resource_discount ENUM('coffee', 'spreadsheets', 'budget', 'connections', 'time') NOT NULL,
    office_prestige TINYINT UNSIGNED NOT NULL DEFAULT 0,
    cost_coffee TINYINT UNSIGNED NOT NULL DEFAULT 0,
    cost_spreadsheets TINYINT UNSIGNED NOT NULL DEFAULT 0,
    cost_budget TINYINT UNSIGNED NOT NULL DEFAULT 0,
    cost_connections TINYINT UNSIGNED NOT NULL DEFAULT 0,
    cost_time TINYINT UNSIGNED NOT NULL DEFAULT 0,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cards_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS executives (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(64) NOT NULL,
    name VARCHAR(128) NOT NULL,
    portrait_asset VARCHAR(128) DEFAULT NULL,
    office_prestige TINYINT UNSIGNED NOT NULL DEFAULT 3,
    required_coffee TINYINT UNSIGNED NOT NULL DEFAULT 0,
    required_spreadsheets TINYINT UNSIGNED NOT NULL DEFAULT 0,
    required_budget TINYINT UNSIGNED NOT NULL DEFAULT 0,
    required_connections TINYINT UNSIGNED NOT NULL DEFAULT 0,
    required_time TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_executives_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_cards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id BIGINT UNSIGNED NOT NULL,
    card_id BIGINT UNSIGNED NOT NULL,
    tier TINYINT UNSIGNED NOT NULL,
    location ENUM('deck', 'market', 'reserved', 'purchased', 'discarded') NOT NULL DEFAULT 'deck',
    owner_game_player_id BIGINT UNSIGNED NULL,
    deck_position SMALLINT UNSIGNED NULL,
    market_slot TINYINT UNSIGNED NULL,
    reserved_at TIMESTAMP NULL DEFAULT NULL,
    purchased_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_game_cards_market_slot (game_id, tier, market_slot),
    KEY idx_game_cards_owner (owner_game_player_id),
    CONSTRAINT fk_game_cards_game
        FOREIGN KEY (game_id) REFERENCES games (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_game_cards_card
        FOREIGN KEY (card_id) REFERENCES cards (id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_game_cards_owner_game_player
        FOREIGN KEY (owner_game_player_id) REFERENCES game_players (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_executives (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id BIGINT UNSIGNED NOT NULL,
    executive_id BIGINT UNSIGNED NOT NULL,
    slot_order TINYINT UNSIGNED NOT NULL,
    owner_game_player_id BIGINT UNSIGNED NULL,
    claimed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_game_executives_slot (game_id, slot_order),
    UNIQUE KEY uq_game_executives_claim (game_id, executive_id),
    CONSTRAINT fk_game_executives_game
        FOREIGN KEY (game_id) REFERENCES games (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_game_executives_executive
        FOREIGN KEY (executive_id) REFERENCES executives (id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_game_executives_owner_game_player
        FOREIGN KEY (owner_game_player_id) REFERENCES game_players (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_resource_bank (
    game_id BIGINT UNSIGNED PRIMARY KEY,
    coffee TINYINT UNSIGNED NOT NULL DEFAULT 0,
    spreadsheets TINYINT UNSIGNED NOT NULL DEFAULT 0,
    budget TINYINT UNSIGNED NOT NULL DEFAULT 0,
    connections TINYINT UNSIGNED NOT NULL DEFAULT 0,
    time TINYINT UNSIGNED NOT NULL DEFAULT 0,
    executive_favor TINYINT UNSIGNED NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_game_resource_bank_game
        FOREIGN KEY (game_id) REFERENCES games (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS player_resources (
    game_player_id BIGINT UNSIGNED PRIMARY KEY,
    coffee TINYINT UNSIGNED NOT NULL DEFAULT 0,
    spreadsheets TINYINT UNSIGNED NOT NULL DEFAULT 0,
    budget TINYINT UNSIGNED NOT NULL DEFAULT 0,
    connections TINYINT UNSIGNED NOT NULL DEFAULT 0,
    time TINYINT UNSIGNED NOT NULL DEFAULT 0,
    executive_favor TINYINT UNSIGNED NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_player_resources_game_player
        FOREIGN KEY (game_player_id) REFERENCES game_players (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_turns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id BIGINT UNSIGNED NOT NULL,
    round_number SMALLINT UNSIGNED NOT NULL,
    turn_number SMALLINT UNSIGNED NOT NULL,
    game_player_id BIGINT UNSIGNED NOT NULL,
    action_type VARCHAR(64) NOT NULL,
    action_payload JSON NULL,
    was_legal TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY uq_game_turns_turn (game_id, turn_number),
    CONSTRAINT fk_game_turns_game
        FOREIGN KEY (game_id) REFERENCES games (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_game_turns_game_player
        FOREIGN KEY (game_player_id) REFERENCES game_players (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id BIGINT UNSIGNED NOT NULL,
    event_type VARCHAR(64) NOT NULL,
    actor_game_player_id BIGINT UNSIGNED NULL,
    payload JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_game_events_game_created (game_id, created_at),
    CONSTRAINT fk_game_events_game
        FOREIGN KEY (game_id) REFERENCES games (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_game_events_actor_game_player
        FOREIGN KEY (actor_game_player_id) REFERENCES game_players (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_state_snapshots (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id BIGINT UNSIGNED NOT NULL,
    source_event_id BIGINT UNSIGNED NULL,
    snapshot_json JSON NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_game_state_snapshots_game_created (game_id, created_at),
    CONSTRAINT fk_game_state_snapshots_game
        FOREIGN KEY (game_id) REFERENCES games (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_game_state_snapshots_source_event
        FOREIGN KEY (source_event_id) REFERENCES game_events (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO schema_migrations (version, description)
VALUES ('001_initial_schema', 'Create the initial Watercooler relational schema')
ON DUPLICATE KEY UPDATE description = VALUES(description);
