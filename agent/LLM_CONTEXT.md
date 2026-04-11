# LLM_CONTEXT.md

## Purpose

This file tells agents what should stay in normal working context, what should be loaded only when relevant, and what should generally be avoided for token efficiency.

## Always Load

- `AGENTS.md`
  Root guardrails, repo phase, source-of-truth order, and file-placement rules.
- `agent/LLM_CONTEXT.md`
  Context-loading rules for the repository.
- `agent/AGENT_WORKFLOW.md`
  Shared workflow behavior for step tracking, transcript maintenance, and adapter precedence.
- `agent/STEPS.md`
  Active working set.

## Load As Needed

- `README.md`
  Human-oriented repo overview and current implementation status.
- `agent/STEP_BACKLOG.md`
  Longer-range outstanding steps.
- `agent/STEP_ARCHIVE.md`
  Completed step history.
- `agent/TRANSCRIPTION.md`
  Conversation history. Load when updating the transcript, recovering prior decisions, or preparing a commit.
- `agent/TRANSCRIPTION_2.md`
  Temporary continuation transcript. Prefer this as the active transcript target while the transcription optimization migration is in progress.
- `resources/planning/JOBS.md`
  Job-to-step map for broader planning context.
- `resources/planning/`
  Focused planning docs for architecture, rules, frontend, backend, realtime, data model, sessions, theme, and roadmap.
- `resources/setup/local-development.md`
  Setup, run, and validation commands.
- `resources/INITIAL_PROMPT.md`
  Original broad project brief.
- `agent/skills/`
  Shared, system-agnostic skill definitions and their shared references.
- `.github/copilot/skills/transcription-sync/`
  Copilot transcript-maintenance pointer and any required tool-specific metadata.
- `.codex/skills/transcription-sync/`
  Codex transcript-maintenance pointer and any required tool-specific metadata.

## Never Load

- `.git/`
  Repository internals.
- `node_modules/`
  Derived dependency contents. Use manifests instead.
- `vendor/`
  Derived PHP dependency contents. Use manifests instead.
- `dist/`
  Generated frontend output unless explicitly requested.
- `build/`
  Generated build output unless explicitly requested.
- `coverage/`
  Derived test artifacts unless explicitly requested.
- `tmp/`
  Temporary files unless directly relevant.
- `logs/`
  Large runtime output unless debugging requires it.

## Documentation Routing

- Keep root-level guardrails and the strict root-file constraint in `AGENTS.md`.
- Keep context-loading and documentation-routing guidance in `agent/LLM_CONTEXT.md`.
- Keep workflow behavior in `agent/AGENT_WORKFLOW.md`.
- Keep shared skill definitions in `agent/skills/`.
- Keep human onboarding in `README.md`.
- Keep application planning in `resources/planning/`.
- Keep agent-operating records in `agent/`.

## Working Notes

- Prefer the narrowest planning file that answers the current question.
- Move files out of `Always Load` as soon as they stop being needed for most steps.
- Prefer shared workflow files over tool-specific adapters when both exist.
- For transcript maintenance, prefer using the transcription helper scripts to identify the active target and latest recorded entry before loading transcript files directly.
