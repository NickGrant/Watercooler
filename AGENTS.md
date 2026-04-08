# AGENTS.md

## Purpose

This repository is in a documentation-first planning phase for the Watercooler project. No application code should be generated until the planning docs are reviewed and accepted.

Watercooler is a browser-based multiplayer board game adaptation of a Splendor-style ruleset with an absurd corporate satire theme.

## Current Phase

The repository currently contains:

- the original project brief in `resources/INITIAL_PROMPT.md`
- human-facing project documentation in `README.md`
- feature and architecture planning docs in `resources/planning/`

The repository does not yet contain:

- Angular application code
- PHP API implementation
- PHP WebSocket service
- MySQL schema or migrations
- production assets

## Agent Goals

When working in this repository, optimize for:

1. Preserving Splendor-equivalent gameplay rules
2. Maintaining server-authoritative multiplayer architecture
3. Keeping documentation and implementation modular
4. Preserving the Watercooler theme without reducing gameplay clarity

## Source Of Truth

Use the documentation in this order when making future decisions:

1. `resources/planning/gameplay-rules.md`
2. `resources/planning/architecture.md`
3. `resources/planning/backend-api.md`
4. `resources/planning/realtime.md`
5. `resources/planning/frontend.md`
6. `resources/planning/data-model.md`
7. `resources/planning/content-and-theme.md`
8. `README.md`
9. `resources/INITIAL_PROMPT.md`

If these sources conflict, prefer the more focused planning document over the broader brief and note the discrepancy in your changes.

## Working Agreements

- Do not change the game into a mechanically different design.
- Do not add account systems, monetization, or unrelated platform features unless explicitly requested.
- Do not connect clients to WebSockets before the join flow succeeds.
- Keep rules logic, theme content, persistence, and transport layers separated.
- Keep docs updated when implementation decisions materially change the plan.

## Documentation Map

- `README.md`: human overview and repo orientation
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
