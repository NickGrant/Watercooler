# Watercooler

Watercooler is a planned browser-based multiplayer board game inspired by Splendor and reskinned as an absurd corporate satire. Players join a game by URL, build a tiny office-worker avatar, collect office resources, acquire Workplace Advantages, attract Executives, and race for Office Prestige.

This repository has moved from documentation-first planning into bootstrap implementation. The Angular frontend shell, the PHP HTTP API scaffold, the PHP realtime scaffold, and the initial MySQL schema design now exist.

## Project Intent

The planned MVP will use:

- Angular for the frontend
- PHP for the HTTP API
- PHP for a separate WebSocket service
- MySQL for persistence
- Angular Material as a base UI layer with a retro intranet skin

Core product goals:

- preserve Splendor-style gameplay structure
- support realtime multiplayer through shared game URLs
- keep the server authoritative for gameplay state
- deliver a readable, funny office-satire presentation

## Repository Structure

- `AGENTS.md`: guidance for coding agents working in this repo
- `agent/`: agent-facing workflow, context, tasks, and transcript files
- `resources/`: application-facing planning and documentation files
- `resources/INITIAL_PROMPT.md`: original project brief
- `resources/planning/`: split planning docs for targeted context
- `resources/setup/local-development.md`: local setup, env, and validation commands
- `docker-compose.yml`: local container orchestration for the scaffolded stack
- `.github/copilot-instructions.md`: thin Copilot-specific adapter to the shared workflow
- `.github/copilot/skills/transcription-sync/`: Copilot-side skill for transcript updates
- `.codex/skills/transcription-sync/`: Codex-side skill for transcript updates

## Planning Docs

Use `resources/planning/index.md` as the entry point for detailed planning. The planning docs are intentionally broken into smaller files so future implementation work can load only the context it needs.

Current planning areas include:

- architecture
- gameplay rules and thematic mapping
- frontend structure
- backend API design
- realtime service design
- data model and persistence
- lobby and session flow
- content, theme, and avatar direction
- phased roadmap

## Current Status

This repo now contains planning documentation plus the first implementation scaffolds for the Angular frontend, PHP HTTP API, PHP realtime service, database migration layer, and Docker-based local bootstrap.

Immediate non-code goals completed here:

- create a human-readable project overview
- create an agent-readable operating guide
- split the initial prompt into focused planning docs
- add repo operating files for context, tasks, features, and transcript maintenance
- initialize version control for the project

## Repo Workflow

This repository uses a lightweight operating layer so humans and agents can collaborate without loading the entire project every time.

- `agent/AGENT_WORKFLOW.md` defines the shared workflow that should stay system-agnostic where possible.
- `agent/LLM_CONTEXT.md` defines what should always stay in context, what should be loaded only when needed, and what should be avoided.
- `agent/TASKS.md`, `agent/TASK_BACKLOG.md`, and `agent/TASK_ARCHIVE.md` keep task tracking split into active, future, and completed work.
- `resources/planning/FEATURES.md` maps larger product areas to the tasks that implement them.
- `agent/TRANSCRIPTION.md` stores the user-assistant project conversation.
- System-specific adapters live alongside the shared workflow where needed, including Copilot and Codex versions of the `transcription-sync` skill.
- `docker-compose.yml` and the service Dockerfiles provide a consistent local environment for the current scaffolds.
- `resources/setup/local-development.md` is the main setup reference for running the scaffold today.

## Next Phase

When implementation begins, the expected first wave of work is:

1. scaffold the Angular frontend, PHP API, PHP WebSocket service, and project structure
2. define MySQL schema and migrations
3. implement game creation, slug generation, and join flow
4. establish lobby flow and server-authoritative realtime messaging

## Notes

- Gameplay should remain mechanically faithful to Splendor.
- Theme changes should improve clarity and flavor, not alter the core strategy loop.
- The project is session-based and should avoid account-system complexity.
