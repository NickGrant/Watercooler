# AGENT_WORKFLOW.md

## Purpose

This file defines repository workflow behavior that should stay shared across agent systems. Keep system-specific files thin and point back here whenever possible.

## Workflow Rules

### Step Tracking

When step status changes:

- update `agent/STEPS.md` for the active working set
- move future work to `agent/STEP_BACKLOG.md` when it does not need to stay in normal context
- move completed work to `agent/STEP_ARCHIVE.md`
- update `resources/planning/JOBS.md` when job-to-step mapping changes
- mark a job as `Complete` in `resources/planning/JOBS.md` when all of its steps are complete
- keep job statuses accurate as work moves from not started to in progress to complete

### Job Execution

For multi-step job work:

- evaluate the job for independent parallel work before starting implementation
- run independent step lanes in parallel when they do not create avoidable overlap or merge risk
- commit each step separately once that step's scope is complete and validated
- notify the user when the full job is complete, then wait for more instructions
- stop and ask for information whenever required context is missing or a risky assumption would otherwise be necessary

### Transcript Maintenance

Before creating a commit:

- review whether `README.md`, `AGENTS.md`, and `agent/LLM_CONTEXT.md` need updates because of the work being committed
- update any of those source-of-truth docs when the change affects their current guidance, repo description, or navigation expectations
- use the shared `transcription-sync` skill to update the active repository transcript target
- ensure the latest visible user and assistant messages are recorded before the commit is created

Before ending a session or clearing context:

- make a best-effort pass to use the shared `transcription-sync` skill to append any still-missing visible conversation to the active repository transcript target

### Documentation Hygiene

- keep always-loaded docs concise
- move navigation-heavy detail to `agent/LLM_CONTEXT.md`
- move human onboarding detail to `README.md`
- move application-specific detail to the narrowest file under `resources/`
- keep shared skill definitions in `agent/skills/`
- keep system-specific skill directories as thin pointers or metadata adapters back to `agent/skills/`

### Search Boundaries

- exclude directories listed under `Never Load` in `agent/LLM_CONTEXT.md` from routine search and code-exploration commands unless the step explicitly requires them
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
