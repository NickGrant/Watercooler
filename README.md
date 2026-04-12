# Watercooler

Watercooler is a browser-based multiplayer board game inspired by Splendor and reskinned as absurd corporate satire. Players join by URL, build a tiny office-worker avatar, collect office resources, acquire Workplace Advantages, attract Executives, and race for Office Prestige.

## Status

The project is deployed and has now been played by real external players.

Watercooler is still in active implementation, but the repo has moved out of pure prototype mode and into a mixed delivery-and-hardening phase: new features are still landing, while code quality, refactoring, operational safety, and maintainability now matter much more than they did during initial scaffolding.

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
- deployed shared-hosting release with live-player validation
- layered PNG avatar pipeline and shared-canvas normalization
- post-launch playtest fixes across action recovery, avatar validation, and endgame presentation
- stale-session autopurge runtime for shared-hosting maintenance

## Collaboration Mode

The project is now developed through a mixed workflow:

- the user may directly edit, refactor, or harden code by hand
- agents may still implement features, write utilities, add tests, and prepare cleanup passes
- repo guidance and transcript history should stay current enough to support future tooling that reconstructs project history from commits plus transcript entries

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
- Scheduled maintenance can now purge games inactive for more than 48 hours through `backend/api/bin/purge-stale-games.php`.
- Theme changes should improve clarity and flavor, not alter core mechanics.
