SET @has_legacy_body_option = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'player_avatars'
      AND COLUMN_NAME = 'body_option'
);

SET @has_avatar_option = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'player_avatars'
      AND COLUMN_NAME = 'avatar_option'
);

SET @single_avatar_option_sql = IF(
    @has_legacy_body_option = 1 AND @has_avatar_option = 0,
    'ALTER TABLE player_avatars CHANGE COLUMN body_option avatar_option VARCHAR(64) NOT NULL, DROP COLUMN face_option, DROP COLUMN hair_option',
    'SELECT 1'
);

PREPARE single_avatar_option_stmt FROM @single_avatar_option_sql;
EXECUTE single_avatar_option_stmt;
DEALLOCATE PREPARE single_avatar_option_stmt;

INSERT INTO schema_migrations (version, description)
VALUES ('005_single_avatar_option', 'Collapse legacy avatar-part columns into a single avatar option id')
ON DUPLICATE KEY UPDATE description = VALUES(description);
