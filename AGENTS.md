# AGENTS.md

## Purpose

This repository is in a documentation-first planning phase for the Watercooler project. No application code should be generated until the planning docs are reviewed and accepted.

Watercooler is a browser-based multiplayer board game adaptation of a Splendor-style ruleset with an absurd corporate satire theme.

## Current Phase

The repository currently contains:

- the original project brief in `resources/INITIAL_PROMPT.md`
- human-facing project documentation in `README.md`
- feature and architecture planning docs in `resources/planning/`
- repo operating files for agent context, tasks, and transcription in `agent/`
- an initial Angular frontend scaffold in `frontend/`
- an initial PHP HTTP API scaffold in `backend/api/`
- shared agent workflow guidance plus system-specific adapters for transcript maintenance

The repository does not yet contain:

- PHP WebSocket service
- MySQL schema or migrations
- production-ready assets or gameplay implementation

## Agent Goals

When working in this repository, optimize for:

1. Preserving Splendor-equivalent gameplay rules
2. Maintaining server-authoritative multiplayer architecture
3. Keeping documentation and implementation modular
4. Preserving the Watercooler theme without reducing gameplay clarity

## Source Of Truth

Use the documentation in this order when making future decisions:

1. `agent/LLM_CONTEXT.md`
2. `agent/AGENT_WORKFLOW.md`
3. `agent/TASKS.md`
4. `resources/planning/FEATURES.md`
5. `resources/planning/gameplay-rules.md`
6. `resources/planning/architecture.md`
7. `resources/planning/backend-api.md`
8. `resources/planning/realtime.md`
9. `resources/planning/frontend.md`
10. `resources/planning/data-model.md`
11. `resources/planning/content-and-theme.md`
12. `README.md`
13. `resources/INITIAL_PROMPT.md`

If these sources conflict, prefer the more focused planning document over the broader brief and note the discrepancy in your changes.

## Working Agreements

- Do not change the game into a mechanically different design.
- Do not add account systems, monetization, or unrelated platform features unless explicitly requested.
- Do not connect clients to WebSockets before the join flow succeeds.
- Keep rules logic, theme content, persistence, and transport layers separated.
- Keep docs updated when implementation decisions materially change the plan.
- Update `agent/TRANSCRIPTION.md` before creating a commit.
- Keep `agent/TASKS.md` small by moving future work to `agent/TASK_BACKLOG.md` and completed work to `agent/TASK_ARCHIVE.md`.

## File Placement

- Keep only `AGENTS.md` and `README.md` at the repository root unless there is a strong system-integration reason not to.
- Put application-related documentation under `resources/`.
- Put application planning breakdowns under `resources/planning/`.
- Put agent-facing operating material under `agent/`.
- Keep system-specific integration files only where the tool requires them, such as `.github/` or `.codex/`.
- When adding a new document, choose the narrowest location that matches its audience and purpose.

## Documentation Map

- `README.md`: human overview and repo orientation
- `agent/LLM_CONTEXT.md`: default context-loading guidance
- `agent/AGENT_WORKFLOW.md`: shared workflow expectations across agent systems
- `agent/TASKS.md`: active task working set
- `agent/TASK_BACKLOG.md`: longer outstanding task list
- `agent/TASK_ARCHIVE.md`: completed task history
- `resources/planning/FEATURES.md`: feature-to-task map
- `agent/TRANSCRIPTION.md`: user and assistant conversation record
- `resources/planning/index.md`: planning entry point
- `resources/planning/architecture.md`: system boundaries and responsibilities
- `resources/planning/gameplay-rules.md`: rules fidelity and terminology mapping
- `resources/planning/frontend.md`: Angular UI, routes, and component plan
- `resources/planning/backend-api.md`: PHP API plan and endpoint responsibilities
- `resources/planning/realtime.md`: PHP WebSocket service plan
- `resources/planning/data-model.md`: persistence and schema direction
- `resources/planning/lobby-and-sessions.md`: join flow, lobby behavior, reconnect rules
- `resources/planning/content-and-theme.md`: copy, visual tone, naming, and avatar direction
- `resources/planning/roadmap.md`: phased delivery plan
- `.github/copilot-instructions.md`: Copilot-specific adapter for shared workflow
- `.github/copilot/skills/transcription-sync/`: Copilot-side transcription skill
- `.codex/skills/transcription-sync/`: Codex-side transcription skill

## Implementation Expectations

When code work begins:

- start with scaffolding and interfaces before deep polish
- add tests for core rules and validation paths early
- prefer small, focused files over large multi-purpose modules
- document assumptions close to the code or in planning docs when the decision affects future work

## Definition Of Done For Future Tasks

A future implementation task should generally include:

- code changes
- any needed test coverage
- documentation updates if behavior or architecture changed
- a short note describing assumptions and follow-up work
