# Local Development

## Purpose

This guide documents the current bootstrap setup for Watercooler across the frontend, API, realtime service, database migration layer, and Docker-based local environment.

## Current Bootstrap Scope

The repository currently provides:

- Angular frontend scaffold in `frontend/`
- PHP HTTP API scaffold in `backend/api/`
- PHP realtime scaffold in `backend/realtime/`
- MySQL migration files in `database/migrations/`
- Docker-based local orchestration in `docker-compose.yml`

## Environment Examples

Service-level environment examples are stored in:

- `backend/api/.env.example`
- `backend/realtime/.env.example`

Current Docker defaults use:

- database host: `db`
- database port: `3306`
- database name: `watercooler`
- database user: `watercooler`
- database password: `watercooler`
- API port: `8080`
- realtime port: `8090`
- frontend port: `4200`

## Direct Local Commands

### Frontend

Run from `frontend/`:

```powershell
npm.cmd install
npm.cmd run build
npm.cmd run start -- --host 0.0.0.0 --port 4200
```

### API

Run from `backend/api/`:

```powershell
composer install
composer lint
composer serve
```

The API scaffold currently exposes:

- `GET /health`
- planned game endpoints returning scaffold responses

### Realtime

Run from `backend/realtime/`:

```powershell
composer install
composer lint
php bin/server.php --once
php bin/server.php
```

Use `--once` for a bootstrap check that prints the configured room and session preview without entering the long-running loop.

### Database

The initial schema lives in:

- `database/migrations/001_initial_schema.sql`

One local validation path used during bootstrap was:

```powershell
& C:\xampp\mysql\bin\mysql.exe -u root -e "DROP DATABASE IF EXISTS watercooler_bootstrap_check; CREATE DATABASE watercooler_bootstrap_check CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; USE watercooler_bootstrap_check; SOURCE C:/xampp/htdocs/watercooler/database/migrations/001_initial_schema.sql; SHOW TABLES; DROP DATABASE watercooler_bootstrap_check;"
```

## Docker Workflow

Build the scaffolded images from the repository root:

```powershell
docker compose build frontend api realtime
```

Validate the composed configuration:

```powershell
docker compose config
```

Start the current local stack:

```powershell
docker compose up
```

Stop it again:

```powershell
docker compose down
```

The database container initializes the current schema automatically from:

- `database/migrations/001_initial_schema.sql`

## Bootstrap Verification Already Completed

These checks were completed during bootstrapping:

- Angular production build succeeded
- API Composer autoload and lint succeeded
- API `/health` responded successfully through PHP's built-in server
- realtime Composer autoload and lint succeeded
- realtime `php bin/server.php --once` succeeded
- initial schema executed successfully against a disposable local MariaDB database
- `docker compose config` succeeded
- Docker image builds for `frontend`, `api`, and `realtime` succeeded

## Next Expected Work

After bootstrap, the next implementation-facing tasks are:

- game creation and slug generation
- home page create-game flow
- join-bootstrap and session handling
- lobby and realtime player synchronization
