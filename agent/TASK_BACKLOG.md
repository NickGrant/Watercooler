# TASK_BACKLOG.md

## Purpose

This file tracks outstanding work that matters to the project but does not need to stay in the default context window.

## Backlog Tasks

### TASK-070

- Name: Replace websocket gameplay sync with smart polling
- Priority: High
- Blocked By: None
- Blocks: TASK-071, TASK-072
- Description: Remove the browser-facing websocket dependency from active-game synchronization and replace it with an adaptive smart-polling flow that works on shared PHP hosting while preserving server-authoritative gameplay state.

### TASK-071

- Name: Add adaptive polling intervals and visibility-aware refresh policy
- Priority: High
- Blocked By: TASK-070
- Blocks: None
- Description: Tune gameplay and lobby polling behavior so the client refreshes aggressively enough for a 4-to-6-player game while reducing unnecessary request volume when the tab is hidden or the room is idle.

### TASK-072

- Name: Remove standalone realtime-service deployment dependency
- Priority: Medium
- Blocked By: TASK-070
- Blocks: None
- Description: Update deployment-oriented code and docs so the app no longer assumes a separately hosted websocket process, including configuration, local-development guidance, and any no-longer-needed realtime transport plumbing.
