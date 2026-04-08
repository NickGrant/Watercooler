# AGENTS.md

## Purpose

Watercooler is a browser-based multiplayer board game adaptation of a Splendor-style ruleset with an absurd corporate satire theme.

This repository is in active implementation. Planning documents still matter, but agents should assume code, tests, and docs are all part of normal work unless a task says otherwise.

## Current Phase

The repository currently includes:

- planning documentation under `resources/`
- agent operating files under `agent/`
- Angular frontend work in `frontend/`
- PHP HTTP API work in `backend/api/`
- PHP realtime service work in `backend/realtime/`
- MySQL schema and migration assets in `database/`
- Docker-based local setup in `docker-compose.yml` and service Dockerfiles

## Core Guardrails

- Preserve Splendor-equivalent gameplay rules.
- Maintain server-authoritative multiplayer architecture.
- Do not add account systems, monetization, or unrelated platform features unless explicitly requested.
- Do not connect clients to WebSockets before the join flow succeeds.
- Keep rules logic, theme content, persistence, and transport layers separated.

## Source Of Truth

Use repository guidance in this order:

1. `agent/LLM_CONTEXT.md`
2. `agent/AGENT_WORKFLOW.md`
3. `agent/TASKS.md`
4. focused planning docs in `resources/planning/`
5. `resources/setup/local-development.md`
6. `resources/INITIAL_PROMPT.md`

If sources conflict, prefer the narrower planning or workflow file over the broader project brief.

## Working Agreements

- Keep docs updated when implementation decisions materially change the plan.
- Update `agent/TRANSCRIPTION.md` before creating a commit.
- Keep `agent/TASKS.md` small by moving future work to `agent/TASK_BACKLOG.md` and completed work to `agent/TASK_ARCHIVE.md`.
- Treat unit tests as required for all new code and behavior changes unless there is a documented technical reason they cannot be added yet.
- When adding or changing code, update or add tests in the same task rather than deferring them.

## File Placement

- Keep only `AGENTS.md` and `README.md` at the repository root unless there is a strong system-integration reason not to.
- Put application-related documentation under `resources/`.
- Put application planning breakdowns under `resources/planning/`.
- Put agent-facing operating material under `agent/`.
- Keep system-specific integration files only where the tool requires them, such as `.github/` or `.codex/`.

## Definition Of Done

A typical task should include:

- implementation changes
- unit test coverage for changed behavior, or a documented reason tests could not be added yet
- documentation updates if behavior or architecture changed
