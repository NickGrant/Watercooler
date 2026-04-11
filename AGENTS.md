# AGENTS.md

## Purpose

Watercooler is a browser-based multiplayer board game adaptation of a Splendor-style ruleset with an absurd corporate satire theme.

This repository is in active implementation. Planning documents still matter, but agents should assume code, tests, and docs are all part of normal work unless a step says otherwise.

## Current Phase

The repository currently includes:

- planning documentation under `resources/`
- agent operating files under `agent/`
- Angular frontend work in `frontend/`
- PHP HTTP API work in `backend/api/`
- MySQL schema and migration assets in `database/`
- Docker-based local setup in `docker-compose.yml` and service Dockerfiles

## Core Guardrails

- Preserve Splendor-equivalent gameplay rules.
- Maintain server-authoritative multiplayer architecture.
- Do not add account systems, monetization, or unrelated platform features unless explicitly requested.
- Treat smart polling over the HTTP API as the default live-sync transport unless a step explicitly reintroduces push transport work.
- Keep rules logic, theme content, persistence, and transport layers separated.

## Source Of Truth

Use repository guidance in this order:

1. `agent/LLM_CONTEXT.md`
2. `agent/AGENT_WORKFLOW.md`
3. `agent/STEPS.md`
4. focused planning docs in `resources/planning/`
5. `resources/setup/local-development.md`
6. `resources/INITIAL_PROMPT.md`

If sources conflict, prefer the narrower planning or workflow file over the broader project brief.

## Working Agreements

- Keep docs updated when implementation decisions materially change the plan.
- Treat unit tests as required for all new code and behavior changes unless there is a documented technical reason they cannot be added yet.
- When adding or changing code, update or add tests in the same step rather than deferring them.
- Use the shared skill definitions under `agent/skills/` when a workflow is captured as a repo skill, such as `transcription-sync` or `step-job-sync`.
- Treat system-specific adapter directories as discovery shims only; shared behavior should live in `AGENTS.md`, `agent/AGENT_WORKFLOW.md`, `agent/LLM_CONTEXT.md`, or `agent/skills/`.
- Follow `agent/AGENT_WORKFLOW.md` for step tracking, transcript maintenance, and job-execution procedure.
- Follow `agent/LLM_CONTEXT.md` for context-loading and documentation-routing rules.

## File Placement

- Keep only `AGENTS.md` and `README.md` at the repository root unless there is a strong system-integration reason not to.
- Follow the documentation-routing rules in `agent/LLM_CONTEXT.md` for where agent, planning, and human-facing docs should live.

## Definition Of Done

A typical step should include:

- implementation changes
- unit test coverage for changed behavior, or a documented reason tests could not be added yet
- documentation updates if behavior or architecture changed
