# AGENT_WORKFLOW.md

## Purpose

This file defines repository workflows that should apply across agent systems whenever possible. System-specific files should stay thin and point back here.

## Shared Workflow Rules

### Context Loading

- Keep `AGENTS.md`, `agent/LLM_CONTEXT.md`, `agent/TASKS.md`, and `resources/planning/FEATURES.md` in normal working context when possible.
- Load `README.md`, `agent/TRANSCRIPTION.md`, `agent/TASK_BACKLOG.md`, `agent/TASK_ARCHIVE.md`, and focused planning docs only when they are relevant to the current task.
- Avoid loading generated or derived directories unless the user explicitly asks.

### Task Tracking

When task status changes:

- update `agent/TASKS.md` for the active working set
- move future work to `agent/TASK_BACKLOG.md` when they no longer need to stay in default context
- move completed work to `agent/TASK_ARCHIVE.md`
- keep `resources/planning/FEATURES.md` aligned when feature-to-task mapping changes

### Transcript Maintenance

Before creating a commit, update `agent/TRANSCRIPTION.md` so it includes the latest visible user and assistant messages.

Before ending a session or clearing context, make a best-effort pass to update `agent/TRANSCRIPTION.md` with any visible conversation that is not yet recorded.

Use the transcript rules already documented in `agent/TRANSCRIPTION.md` and, when available, use a system-specific `transcription-sync` skill instead of improvising the format.

## System Adapters

- Copilot adapter: `.github/copilot-instructions.md`
- Copilot skill: `.github/copilot/skills/transcription-sync/`
- Codex skill: `.codex/skills/transcription-sync/`

## Priority

If a system-specific adapter conflicts with this file, prefer the shared workflow unless the difference is required by the tool itself.
