# Local Development

## Purpose

This guide documents the current bootstrap setup for Watercooler across the frontend, API, database migration layer, and Docker-based local environment.

## Current Bootstrap Scope

The repository currently provides:

- Angular frontend scaffold in `frontend/`
- PHP HTTP API scaffold in `backend/api/`
- MySQL migration files in `database/migrations/`
- Docker-based local orchestration in `docker-compose.yml`

## Environment Examples

Service-level environment examples are stored in:

- `backend/api/.env.example`

Current Docker defaults use:

- database host: `db`
- database port: `3306`
- database name: `watercooler`
- database user: `watercooler`
- database password: `watercooler`
- API port: `8080`
- frontend port: `4200`

## Direct Local Commands

### Frontend

Run from `frontend/`:

```powershell
npm.cmd install
npm.cmd run build
npm.cmd test -- --watch=false --browsers=ChromeHeadless
npm.cmd run start -- --host 0.0.0.0 --port 4200
```

### API

Run from `backend/api/`:

```powershell
composer install
composer lint
composer test
composer serve
```

The API scaffold currently exposes:

- `GET /health`
- `POST /api/games`
- `GET /api/games/{slug}`
- `POST /api/games/{slug}/join-bootstrap`
- `GET /api/games/{slug}/state`
- `POST /api/games/{slug}/bug-reports`

### Database

The current schema and follow-up migrations live in:

- `database/migrations/001_initial_schema.sql`
- `database/migrations/002_seed_cards_and_executives.sql`
- `database/migrations/003_bug_report_capture.sql`
- `database/migrations/004_executive_portrait_asset_compat.sql`

One local validation path used during bootstrap was:

```powershell
& C:\xampp\mysql\bin\mysql.exe -u root -e "DROP DATABASE IF EXISTS watercooler_bootstrap_check; CREATE DATABASE watercooler_bootstrap_check CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; USE watercooler_bootstrap_check; SOURCE C:/xampp/htdocs/watercooler/database/migrations/001_initial_schema.sql; SOURCE C:/xampp/htdocs/watercooler/database/migrations/002_seed_cards_and_executives.sql; SOURCE C:/xampp/htdocs/watercooler/database/migrations/003_bug_report_capture.sql; SOURCE C:/xampp/htdocs/watercooler/database/migrations/004_executive_portrait_asset_compat.sql; SHOW TABLES; DROP DATABASE watercooler_bootstrap_check;"
```

When updating an existing local or hosted database, apply every migration in order rather than assuming the initial schema file is sufficient. In particular, older databases that predate `004_executive_portrait_asset_compat.sql` can load lobby data successfully but will fail at runtime when start-game or active-state queries request `executives.portrait_asset`.

## Docker Workflow

Build the scaffolded images from the repository root:

```powershell
docker compose build frontend api
```

Validate the composed configuration:

```powershell
docker compose config
```

Start the current local stack:

```powershell
docker compose up
```

The Angular dev server proxies `/api/*` and `/health` requests to the API service during development, so browser traffic from `http://localhost:4200` can reach the PHP API without hardcoded frontend base URLs.

Stop it again:

```powershell
docker compose down
```

The database container initializes the current schema automatically from:

- `database/migrations/001_initial_schema.sql`
- `database/migrations/002_seed_cards_and_executives.sql`
- `database/migrations/003_bug_report_capture.sql`
- `database/migrations/004_executive_portrait_asset_compat.sql`

## Bootstrap Verification Already Completed

These checks were completed during bootstrapping:

- Angular production build succeeded
- Angular unit tests succeeded
- API Composer autoload and lint succeeded
- API PHPUnit suite succeeded
- API `/health` responded successfully through PHP's built-in server
- API `POST /api/games/{slug}/join-bootstrap` responded successfully through a Docker-run API container backed by MariaDB
- initial schema executed successfully against a disposable local MariaDB database
- `docker compose config` succeeded
- Docker image builds for `frontend` and `api` succeeded

## Next Expected Work

After bootstrap, the next implementation-facing tasks are:

- game creation and slug generation
- home page create-game flow
- join-bootstrap and session handling
- lobby and smart-polling synchronization
