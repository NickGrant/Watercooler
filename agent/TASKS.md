# TASKS.md

## Purpose

This file keeps the active working set small enough to remain in normal agent context. Move completed tasks to `agent/TASK_ARCHIVE.md` and move lower-priority or farther-future work to `agent/TASK_BACKLOG.md`.

## Active Tasks

### TASK-071

- Name: Add adaptive polling intervals and visibility-aware refresh policy
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Tune gameplay and lobby polling behavior so the client refreshes aggressively enough for a 4-to-6-player game while reducing unnecessary request volume when the tab is hidden or the room is idle.

## Task Management Rules

- Keep this file limited to the current working set.
- Prefer moving future work to `agent/TASK_BACKLOG.md` instead of growing this file indefinitely.
- When a task is completed, move it to `agent/TASK_ARCHIVE.md` and update any linked `blocked by` or `blocks` references in remaining tasks.
