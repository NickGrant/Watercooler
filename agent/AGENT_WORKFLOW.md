# AGENT_WORKFLOW.md

## Purpose

This file defines repository workflow behavior that should stay shared across agent systems. Keep system-specific files thin and point back here whenever possible.

## Workflow Rules

### Task Tracking

When task status changes:

- update `agent/TASKS.md` for the active working set
- move future work to `agent/TASK_BACKLOG.md` when they no longer need to stay in normal context
- move completed work to `agent/TASK_ARCHIVE.md`
- update `resources/planning/FEATURES.md` when feature-to-task mapping changes
- mark a feature as `Complete` in `resources/planning/FEATURES.md` when all of its tasks are complete
- keep feature statuses accurate as work moves from not started to in progress to complete

### Feature Execution

For multi-task feature work:

- evaluate the feature for independent parallel work before starting implementation
- run independent task lanes in parallel when they do not create avoidable overlap or merge risk
- commit each task separately once that task's scope is complete and validated
- notify the user when the full feature is complete, then wait for more instructions
- stop and ask for information whenever required context is missing or a risky assumption would otherwise be necessary

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
- keep shared skill definitions in `agent/skills/`
- keep system-specific skill directories as thin pointers or metadata adapters back to `agent/skills/`

### Search Boundaries

- exclude directories listed under `Never Load` in `agent/LLM_CONTEXT.md` from routine search and code-exploration commands unless the task explicitly requires them
- treat the `Never Load` list in `agent/LLM_CONTEXT.md` as the source of truth for opt-in investigation targets rather than duplicating that directory list here
- when using broad search tools such as `rg`, prefer command patterns that proactively exclude those directories instead of relying on manual filtering after results appear

### Styling Boundaries

- keep component- or page-specific styles in the owning component or page stylesheet
- use the global stylesheet for shared tokens, reset/base rules, and styles intentionally reused across multiple components or pages
- do not move styles into the global stylesheet only to satisfy a component style budget; instead, reduce or reorganize the owning stylesheet while preserving logical ownership

## System Adapters

- Shared skill source: `agent/skills/`
- Copilot skill adapter: `.github/copilot/skills/`
- Codex skill adapter: `.codex/skills/`

## Priority

If a tool-specific adapter conflicts with this file, prefer this file unless the difference is required by the tool itself.
