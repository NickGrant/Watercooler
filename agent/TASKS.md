# TASKS.md

## Purpose

This file keeps the active working set small enough to remain in normal agent context. Move completed tasks to `agent/TASK_ARCHIVE.md` and move lower-priority or farther-future work to `agent/TASK_BACKLOG.md`.

## Active Tasks

### TASK-056

- Name: Rename projects to be more expressive
- Priority: Medium
- Blocked By: None
- Blocks: None
- Description: Rewrite the seeded project names so backlog and completed-project content feels more expressive and thematic during play without changing gameplay balance.

### TASK-057

- Name: Create screenshot skill for game-screen visual capture
- Priority: Medium
- Blocked By: None
- Blocks: None
- Description: Create an agent skill that can trigger a game-screen screenshot through the npm testing stack so UI reviews can be repeated consistently during UAT.

### TASK-058

- Name: Build reusable resource icon component
- Priority: High
- Blocked By: None
- Blocks: TASK-059, TASK-061
- Description: Create a shared resource icon component with regular and small variants using square icon art and a superscript value badge with a solid background so resource display logic is centralized before further board-density cleanup.

## Task Management Rules

- Keep this file limited to the current working set.
- Prefer moving future work to `agent/TASK_BACKLOG.md` instead of growing this file indefinitely.
- When a task is completed, move it to `agent/TASK_ARCHIVE.md` and update any linked `blocked by` or `blocks` references in remaining tasks.
