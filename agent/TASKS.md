# TASKS.md

## Purpose

This file keeps the active working set small enough to remain in normal agent context. Move completed tasks to `agent/TASK_ARCHIVE.md` and move lower-priority or farther-future work to `agent/TASK_BACKLOG.md`.

## Active Tasks

### TASK-036

- Name: Generate Docker-based local development bootstrap
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Add Dockerfiles and a compose-based local development setup for the frontend, API, realtime service, and MySQL so the scaffolded platform can be started consistently.

### TASK-015

- Name: Implement game creation and slug generation API flow
- Priority: High
- Blocked By: None
- Blocks: TASK-016, TASK-019
- Description: Build the API behavior for creating a new game, generating a unique themed slug, and returning the initial redirect target.

### TASK-016

- Name: Build the home page and create-game route flow
- Priority: Medium
- Blocked By: TASK-015
- Blocks: TASK-017
- Description: Implement the first frontend user path from landing page to a newly created game URL, preserving the Watercooler tone without starting gameplay work yet.

### TASK-017

- Name: Implement the pre-join identity and avatar flow
- Priority: High
- Blocked By: TASK-016, TASK-019
- Blocks: TASK-018, TASK-020
- Description: Build the UI and state flow for display name entry, avatar configuration, validation feedback, and accepted join state before websocket connection.

### TASK-018

- Name: Implement lobby UI and start-game controls
- Priority: Medium
- Blocked By: TASK-017, TASK-020
- Blocks: TASK-021
- Description: Build the waiting-room experience showing joined players, avatars, host status, and start-game controls once minimum player rules are met.

## Task Management Rules

- Keep this file limited to the current working set.
- Prefer moving future work to `agent/TASK_BACKLOG.md` instead of growing this file indefinitely.
- When a task is completed, move it to `agent/TASK_ARCHIVE.md` and update any linked `blocked by` or `blocks` references in remaining tasks.
