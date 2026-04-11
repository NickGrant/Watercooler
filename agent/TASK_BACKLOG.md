# TASK_BACKLOG.md

## Purpose

This file tracks outstanding work that matters to the project but does not need to stay in the default context window.

## Backlog Tasks


### TASK-079

- Name: Add bug report persistence schema and storage contract
- Priority: High
- Blocked By: None
- Blocks: TASK-080, TASK-081, TASK-082
- Description: Add the database table and backend repository contract for in-game bug reports, storing unread/read status, message content, optional reply email, created timestamp, reporter display name, room slug, and lightweight debugging snapshot fields that are not reliably recoverable later.

### TASK-080

- Name: Implement bug report submission API flow
- Priority: High
- Blocked By: TASK-079
- Blocks: TASK-081, TASK-082
- Description: Add a server-side submission endpoint and service that validates required message content, accepts an optional reply email, resolves the active player and room context, and persists the report in unread state for later DB-side review.

### TASK-081

- Name: Build floating in-game bug report panel
- Priority: High
- Blocked By: TASK-080
- Blocks: TASK-082
- Description: Add a right-edge floating bug-report tab on the game page that expands into a compact form with optional reply email, required message field, submission feedback, and only the minimum additional client context needed for debugging.

### TASK-082

- Name: Cover bug report flow with automated tests and docs
- Priority: Medium
- Blocked By: TASK-079, TASK-080, TASK-081
- Blocks: None
- Description: Add backend and frontend coverage for bug report submission behavior and update any planning or setup docs needed to reflect the new persistence path and operating assumptions.
