# TASKS.md

## Purpose

This file keeps the active working set small enough to remain in normal agent context. Move completed tasks to `agent/TASK_ARCHIVE.md` and move lower-priority or farther-future work to `agent/TASK_BACKLOG.md`.

## Active Tasks

### TASK-011

- Name: Scaffold Angular frontend workspace
- Priority: High
- Blocked By: None
- Blocks: TASK-016, TASK-017, TASK-018
- Description: Create the frontend application shell with routing, Angular Material setup, and room for the pre-join, lobby, and game experiences.

### TASK-012

- Name: Scaffold PHP HTTP API service
- Priority: High
- Blocked By: None
- Blocks: TASK-014, TASK-015, TASK-019
- Description: Establish the API entrypoint, routing approach, configuration pattern, and testable service boundaries for request-response features.

### TASK-013

- Name: Scaffold PHP WebSocket service
- Priority: High
- Blocked By: None
- Blocks: TASK-020, TASK-021
- Description: Establish the long-running realtime service skeleton, connection flow, room lifecycle approach, and shared domain integration points.

### TASK-014

- Name: Design initial MySQL schema and migration strategy
- Priority: High
- Blocked By: TASK-012
- Blocks: TASK-015, TASK-019, TASK-020, TASK-021, TASK-036
- Description: Define the first-pass relational model, migration workflow, and seed strategy needed to support games, players, cards, executives, resources, and recovery.

### TASK-036

- Name: Generate Docker-based local development bootstrap
- Priority: High
- Blocked By: TASK-011, TASK-012, TASK-013, TASK-014
- Blocks: None
- Description: Add Dockerfiles and a compose-based local development setup for the frontend, API, realtime service, and MySQL so the scaffolded platform can be started consistently.

### TASK-015

- Name: Implement game creation and slug generation API flow
- Priority: High
- Blocked By: TASK-012, TASK-014
- Blocks: TASK-016, TASK-019
- Description: Build the API behavior for creating a new game, generating a unique themed slug, and returning the initial redirect target.

### TASK-016

- Name: Build the home page and create-game route flow
- Priority: Medium
- Blocked By: TASK-011, TASK-015
- Blocks: TASK-017
- Description: Implement the first frontend user path from landing page to a newly created game URL, preserving the Watercooler tone without starting gameplay work yet.

### TASK-017

- Name: Implement the pre-join identity and avatar flow
- Priority: High
- Blocked By: TASK-011, TASK-016, TASK-019
- Blocks: TASK-018, TASK-020
- Description: Build the UI and state flow for display name entry, avatar configuration, validation feedback, and accepted join state before websocket connection.

### TASK-018

- Name: Implement lobby UI and start-game controls
- Priority: Medium
- Blocked By: TASK-011, TASK-017, TASK-020
- Blocks: TASK-021
- Description: Build the waiting-room experience showing joined players, avatars, host status, and start-game controls once minimum player rules are met.

## Task Management Rules

- Keep this file limited to the current working set.
- Prefer moving future work to `agent/TASK_BACKLOG.md` instead of growing this file indefinitely.
- When a task is completed, move it to `agent/TASK_ARCHIVE.md` and update any linked `blocked by` or `blocks` references in remaining tasks.
