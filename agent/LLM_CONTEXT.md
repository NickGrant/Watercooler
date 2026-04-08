# LLM_CONTEXT.md

## Purpose

This file helps agents manage context intentionally in this repository. Use it to decide what to keep in context by default, what to load only for a relevant task, and what to avoid loading unless the user explicitly asks.

## Always Load

- `AGENTS.md`
  Agent operating rules, project guardrails, and repo workflow expectations.
- `agent/AGENT_WORKFLOW.md`
  Shared agent workflow rules that are intended to work across Codex, Copilot, and similar systems.
- `agent/LLM_CONTEXT.md`
  Context-loading rules for navigating the repository efficiently.
- `agent/TASKS.md`
  Current active task list that should remain visible during normal work.
- `resources/planning/FEATURES.md`
  High-level feature map that connects implementation work to task groups.

## Load As Needed

- `README.md`
  Human-oriented overview, current repo state, and onboarding summary.
- `agent/TRANSCRIPTION.md`
  Conversation record. Load when continuing the transcript, reviewing prior decisions, or preparing a commit.
- `agent/TASK_BACKLOG.md`
  Extended outstanding work that is intentionally kept out of the default context set.
- `agent/TASK_ARCHIVE.md`
  Completed task history.
- `resources/INITIAL_PROMPT.md`
  Original broad project brief.
- `resources/planning/`
  Focused planning documents for architecture, gameplay, frontend, backend, realtime, persistence, sessions, theme, and roadmap.
- `.github/copilot-instructions.md`
  Thin Copilot-specific adapter that points back to the shared workflow and Copilot skill path.
- `.github/copilot/skills/transcription-sync/`
  Repo-local Copilot skill for updating `agent/TRANSCRIPTION.md`.
- `.codex/skills/transcription-sync/`
  Repo-local Codex-oriented version of the transcript update skill.

## Never Load

- `.git/`
  Repository internals and object storage are not useful for normal context loading.
- `node_modules/`
  Derived dependency contents should not be loaded; use manifest files instead.
- `vendor/`
  Derived PHP dependency contents should not be loaded; use manifest files instead.
- `dist/`
  Generated build output should not be loaded unless the user explicitly asks to inspect it.
- `build/`
  Generated build output should not be loaded unless explicitly requested.
- `coverage/`
  Derived test artifacts should not be loaded unless the user asks for them.
- `tmp/`
  Temporary files should be ignored unless they are the direct subject of the task.
- `logs/`
  Large runtime output should not be loaded unless the task is specifically about diagnostics.

## Working Notes

- Prefer the smallest focused planning doc that answers the current question.
- Update this file when new high-signal repo files become part of the normal workflow.
- If a file moves from active use to archival use, move it from `Always Load` to `Load As Needed`.
- Prefer system-agnostic workflow files over tool-specific adapters when both exist.
