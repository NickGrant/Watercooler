SET @has_executive_portrait_asset = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'executives'
      AND COLUMN_NAME = 'portrait_asset'
);

SET @portrait_asset_sql = IF(
    @has_executive_portrait_asset = 0,
    'ALTER TABLE executives ADD COLUMN portrait_asset VARCHAR(128) DEFAULT NULL AFTER name',
    'SELECT 1'
);

PREPARE portrait_asset_stmt FROM @portrait_asset_sql;
EXECUTE portrait_asset_stmt;
DEALLOCATE PREPARE portrait_asset_stmt;

INSERT INTO schema_migrations (version, description)
VALUES ('004_executive_portrait_asset_compat', 'Backfill executive portrait asset support for older databases')
ON DUPLICATE KEY UPDATE description = VALUES(description);
