# Watercooler

Watercooler is a browser-based multiplayer board game inspired by Splendor and reskinned as absurd corporate satire. Players join by URL, build a tiny office-worker avatar, collect office resources, acquire Workplace Advantages, attract Executives, and race for Office Prestige.

## Status

The project is in active implementation.

Current repo state includes:

- planning docs under `resources/`
- Angular frontend work in `frontend/`
- PHP HTTP API work in `backend/api/`
- PHP realtime service work in `backend/realtime/`
- MySQL schema and migration assets in `database/`
- Docker-based local setup in `docker-compose.yml`

Recent implementation work includes:

- game creation and slug generation
- frontend create-game and pre-join flow
- join-bootstrap API and temporary player sessions
- realtime room join and lobby presence primitives
- waiting-room lobby UI

## Stack

- Angular frontend
- PHP HTTP API
- PHP realtime service
- MySQL persistence
- Angular Material as the current UI base

## Repository Layout

- `AGENTS.md`: root guide for coding agents
- `agent/`: agent workflow, context, tasks, and transcript files
- `resources/`: application docs and planning material
- `resources/planning/`: focused planning docs
- `resources/setup/local-development.md`: setup and validation commands
- `database/`: schema and migration files
- `frontend/`: Angular app
- `backend/api/`: HTTP API
- `backend/realtime/`: realtime service

## Documentation

- Start with `resources/planning/index.md` for application planning.
- Use `resources/setup/local-development.md` for local commands and validation steps.
- Agent-specific operating guidance lives in `agent/`.

## Notes

- Gameplay should remain mechanically faithful to Splendor.
- The project is session-based and should avoid account-system complexity.
- Theme changes should improve clarity and flavor, not alter core mechanics.
