# TASK_BACKLOG.md

## Purpose

This file tracks outstanding work that matters to the project but does not need to stay in the default context window.

## Backlog Tasks

### TASK-055

- Name: Implement true websocket gameplay transport
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Replace the current scaffolded/polling-based active-game refresh path with a real websocket transport that pushes authoritative gameplay state changes to every connected client.

### TASK-066

- Name: Tighten project cards and increase per-row density
- Priority: High
- Blocked By: TASK-065
- Blocks: None
- Description: Make project cards narrower and denser so more backlog items can fit on a single row without sacrificing readability.

### TASK-067

- Name: Remove border from take-selected-resources button
- Priority: Medium
- Blocked By: None
- Blocks: None
- Description: Update the take-selected-resources control styling so it matches the other primary actions instead of reading as a bordered special case.

### TASK-068

- Name: Split resource bank into resources and Executive Favor columns
- Priority: High
- Blocked By: TASK-065
- Blocks: None
- Description: Rework the resource-bank layout so the main resource acquisition flow lives in one column while Executive Favor is separated into its own clearer column.

### TASK-069

- Name: Redesign executive cards from mock reference
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Rebuild the executive card presentation to match the structural intent of `resources/ui-mocks/executive-card-v1/mock.png`, using the portrait as the dominant visual, a bordered/stylistic portrait background treatment, a distinct executive-name band, a separate prestige number block, and requirement icons that visually indicate when their thresholds are satisfied.
