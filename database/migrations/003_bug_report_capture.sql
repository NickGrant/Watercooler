CREATE TABLE IF NOT EXISTS bug_reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id BIGINT UNSIGNED NOT NULL,
    reporter_game_player_id BIGINT UNSIGNED NULL,
    current_turn_game_player_id BIGINT UNSIGNED NULL,
    room_slug VARCHAR(80) NOT NULL,
    reporter_display_name VARCHAR(64) NOT NULL,
    reporter_seat_order TINYINT UNSIGNED NULL,
    reply_email VARCHAR(255) NULL,
    message_body TEXT NOT NULL,
    status ENUM('unread', 'read') NOT NULL DEFAULT 'unread',
    game_status_snapshot ENUM('lobby', 'active', 'completed', 'abandoned') NOT NULL,
    game_phase_snapshot ENUM('pre_join', 'lobby', 'active', 'endgame', 'completed') NOT NULL,
    client_user_agent VARCHAR(512) NULL,
    read_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_bug_reports_status_created (status, created_at),
    KEY idx_bug_reports_game_created (game_id, created_at),
    CONSTRAINT fk_bug_reports_game
        FOREIGN KEY (game_id) REFERENCES games (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_bug_reports_reporter_game_player
        FOREIGN KEY (reporter_game_player_id) REFERENCES game_players (id)
        ON DELETE SET NULL,
    CONSTRAINT fk_bug_reports_current_turn_game_player
        FOREIGN KEY (current_turn_game_player_id) REFERENCES game_players (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO schema_migrations (version, description)
VALUES ('003_bug_report_capture', 'Add in-game bug report persistence')
ON DUPLICATE KEY UPDATE description = VALUES(description);
