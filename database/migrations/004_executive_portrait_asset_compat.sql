ALTER TABLE executives
    ADD COLUMN IF NOT EXISTS portrait_asset VARCHAR(128) DEFAULT NULL AFTER name;

INSERT INTO schema_migrations (version, description)
VALUES ('004_executive_portrait_asset_compat', 'Backfill executive portrait asset support for older databases')
ON DUPLICATE KEY UPDATE description = VALUES(description);
