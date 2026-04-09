# TASK_ARCHIVE.md

## Purpose

This file stores completed work so `agent/TASKS.md` can stay small and useful.

## Completed Tasks

### TASK-023

- Name: Implement project-claiming and Executive Favor flow
- Priority: High
- Blocked By: None
- Blocks: TASK-026, TASK-030
- Description: Add reserve-equivalent behavior, wildcard distribution rules, and reserved-card state management.

### TASK-022

- Name: Implement resource-taking action validation
- Priority: High
- Blocked By: None
- Blocks: TASK-026, TASK-030
- Description: Add server-authoritative validation and state mutation for standard Splendor-equivalent resource-taking actions.

### TASK-021

- Name: Implement game start orchestration
- Priority: High
- Blocked By: None
- Blocks: TASK-022, TASK-023, TASK-024, TASK-025, TASK-026
- Description: Initialize turn order, visible market, executives, bank state, and synchronized start-game payloads once the host starts the match.

### TASK-018

- Name: Implement lobby UI and start-game controls
- Priority: Medium
- Blocked By: TASK-017, TASK-020
- Blocks: TASK-021
- Description: Build the waiting-room experience showing joined players, avatars, host status, and start-game controls once minimum player rules are met.

### TASK-020

- Name: Implement realtime room join and lobby presence sync
- Priority: High
- Blocked By: None
- Blocks: TASK-018, TASK-021, TASK-028
- Description: Connect accepted players to a room by slug, synchronize lobby presence, and support disconnect and reconnect behavior before gameplay starts.

### TASK-017

- Name: Implement the pre-join identity and avatar flow
- Priority: High
- Blocked By: TASK-019
- Blocks: TASK-018, TASK-020
- Description: Build the UI and state flow for display name entry, avatar configuration, validation feedback, and accepted join state before websocket connection.

### TASK-019

- Name: Implement join-bootstrap API and temporary player sessions
- Priority: High
- Blocked By: None
- Blocks: TASK-017, TASK-020, TASK-028
- Description: Validate join requests, enforce game-scoped name uniqueness, issue a temporary player session token, and return the data required for safe websocket connection.

### TASK-016

- Name: Build the home page and create-game route flow
- Priority: Medium
- Blocked By: None
- Blocks: TASK-017
- Description: Implement the first frontend user path from landing page to a newly created game URL, preserving the Watercooler tone without starting gameplay work yet.

### TASK-015

- Name: Implement game creation and slug generation API flow
- Priority: High
- Blocked By: None
- Blocks: TASK-016, TASK-019
- Description: Build the API behavior for creating a new game, generating a unique themed slug, and returning the initial redirect target.

### TASK-037

- Name: Enforce unit-test expectations and add baseline coverage
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Update `AGENTS.md` to require unit tests for new code and add baseline automated tests for the existing frontend, API, and realtime scaffolds.

### TASK-035

- Name: Add developer setup, environment examples, and test commands
- Priority: Medium
- Blocked By: TASK-036
- Blocks: None
- Description: Document local setup, configuration, and repeatable developer commands once the first runnable scaffolding and Docker bootstrap exist.

### TASK-036

- Name: Generate Docker-based local development bootstrap
- Priority: High
- Blocked By: TASK-014
- Blocks: None
- Description: Add Dockerfiles and a compose-based local development setup for the frontend, API, realtime service, and MySQL so the scaffolded platform can be started consistently.

### TASK-014

- Name: Design initial MySQL schema and migration strategy
- Priority: High
- Blocked By: None
- Blocks: TASK-015, TASK-019, TASK-020, TASK-021, TASK-036
- Description: Define the first-pass relational model, migration workflow, and seed strategy needed to support games, players, cards, executives, resources, and recovery.

### TASK-013

- Name: Scaffold PHP WebSocket service
- Priority: High
- Blocked By: None
- Blocks: TASK-020, TASK-021
- Description: Establish the long-running realtime service skeleton, connection flow, room lifecycle approach, and shared domain integration points.

### TASK-012

- Name: Scaffold PHP HTTP API service
- Priority: High
- Blocked By: None
- Blocks: TASK-014, TASK-015, TASK-019
- Description: Establish the API entrypoint, routing approach, configuration pattern, and testable service boundaries for request-response features.

### TASK-011

- Name: Scaffold Angular frontend workspace
- Priority: High
- Blocked By: None
- Blocks: TASK-016, TASK-017, TASK-018
- Description: Create the frontend application shell with routing, Angular Material setup, and room for the pre-join, lobby, and game experiences.

### TASK-010

- Name: Define implementation directory layout and service boundaries
- Priority: High
- Blocked By: None
- Blocks: TASK-011, TASK-012, TASK-013, TASK-014
- Description: Finalize the intended folder layout for the Angular frontend, PHP API, PHP realtime service, shared domain logic, and database assets so scaffolding work starts from a clear structure.

### TASK-001

- Name: Convert the original brief into repo planning documents
- Priority: High
- Blocked By: None
- Blocks: TASK-002
- Description: Break the broad project prompt into smaller planning documents under `resources/planning/` so future work can load focused context.

### TASK-002

- Name: Create repository overview and agent operating guide
- Priority: High
- Blocked By: TASK-001
- Blocks: TASK-003
- Description: Add `README.md` and `AGENTS.md` so both humans and agents have a clear starting point for the project.

### TASK-003

- Name: Initialize git and commit the documentation foundation
- Priority: High
- Blocked By: TASK-002
- Blocks: TASK-004
- Description: Start repository history with a documentation-only commit before implementation work begins.

### TASK-004

- Name: Add repo operating files for context, tasks, features, and transcription
- Priority: High
- Blocked By: TASK-003
- Blocks: TASK-010
- Description: Add the context, task-tracking, feature-planning, and transcription operating files plus the initial transcript-maintenance workflow.
