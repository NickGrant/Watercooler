# AGENT_WORKFLOW.md

## Purpose

This file defines repository workflow behavior that should stay shared across agent systems. Keep system-specific files thin and point back here whenever possible.

## Workflow Rules

### Task Tracking

When task status changes:

- update `agent/TASKS.md` for the active working set
- move future work to `agent/TASK_BACKLOG.md` when they no longer need to stay in normal context
- move completed work to `agent/TASK_ARCHIVE.md`
- update `resources/planning/FEATURES.md` only when feature-to-task mapping changes

### Transcript Maintenance

Before creating a commit:

- update `agent/TRANSCRIPTION.md`
- record the latest visible user and assistant messages
- follow the formatting rules already documented in `agent/TRANSCRIPTION.md`

Before ending a session or clearing context:

- make a best-effort pass to append any still-missing visible conversation to `agent/TRANSCRIPTION.md`

### Documentation Hygiene

- keep always-loaded docs concise
- move navigation-heavy detail to `agent/LLM_CONTEXT.md`
- move human onboarding detail to `README.md`
- move application-specific detail to the narrowest file under `resources/`

## System Adapters

- Copilot adapter: `.github/copilot-instructions.md`
- Copilot skill: `.github/copilot/skills/transcription-sync/`
- Codex skill: `.codex/skills/transcription-sync/`

## Priority

If a tool-specific adapter conflicts with this file, prefer this file unless the difference is required by the tool itself.
