# Copilot Instructions

Follow the shared repository workflow in `agent/AGENT_WORKFLOW.md`, plus the repo guidance in `AGENTS.md` and the loading rules in `agent/LLM_CONTEXT.md`.

Before creating a commit, use the shared `transcription-sync` skill defined in `agent/skills/transcription-sync/`.

The Copilot-local directory at `.github/copilot/skills/transcription-sync/` is only a thin adapter that points back to the shared skill definition.

Use this file only for Copilot-specific adaptation. Shared workflow rules should live in `agent/AGENT_WORKFLOW.md`.
