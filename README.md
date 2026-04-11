# Watercooler

Watercooler is a browser-based multiplayer board game inspired by Splendor and reskinned as absurd corporate satire. Players join by URL, build a tiny office-worker avatar, collect office resources, acquire Workplace Advantages, attract Executives, and race for Office Prestige.

## Status

The project is in active implementation.

Current repo state includes:

- planning docs under `resources/`
- Angular frontend work in `frontend/`
- PHP HTTP API work in `backend/api/`
- MySQL schema and migration assets in `database/`
- Docker-based local setup in `docker-compose.yml`

Recent implementation work includes:

- game creation and slug generation
- frontend create-game and pre-join flow
- join-bootstrap API and temporary player sessions
- smart polling-based lobby and game-state refresh
- waiting-room and in-game board UI
- in-game bug-report capture

## Stack

- Angular frontend
- PHP HTTP API
- MySQL persistence
- Angular Material as the current UI base
- polling-first synchronization tuned for small shared-hosting rooms

## Repository Layout

- `AGENTS.md`: root guide for coding agents
- `agent/`: agent workflow, context, steps, and transcript files
- `resources/`: application docs and planning material
- `resources/planning/`: focused planning docs
- `resources/setup/local-development.md`: setup and validation commands
- `database/`: schema and migration files
- `frontend/`: Angular app
- `backend/api/`: HTTP API

## Documentation

- Start with `resources/planning/index.md` for application planning.
- Use `resources/setup/local-development.md` for local commands and validation steps.
- Agent-specific operating guidance lives in `agent/`.

## Notes

- Gameplay should remain mechanically faithful to Splendor.
- The project is session-based and should avoid account-system complexity.
- The default deployment path is shared-hosting friendly and does not require a separate live-sync process.
- Theme changes should improve clarity and flavor, not alter core mechanics.
