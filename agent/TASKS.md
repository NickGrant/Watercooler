# TASKS.md

## Purpose

This file keeps the active working set small enough to remain in normal agent context. Move completed tasks to `agent/TASK_ARCHIVE.md` and move lower-priority or farther-future work to `agent/TASK_BACKLOG.md`.

## Active Tasks

### TASK-072

- Name: Remove standalone realtime-service deployment dependency
- Priority: Medium
- Blocked By: None
- Blocks: None
- Description: Update deployment-oriented code and docs so the app no longer assumes a separately hosted websocket process, including configuration, local-development guidance, and any no-longer-needed realtime transport plumbing.

## Task Management Rules

- Keep this file limited to the current working set.
- Prefer moving future work to `agent/TASK_BACKLOG.md` instead of growing this file indefinitely.
- When a task is completed, move it to `agent/TASK_ARCHIVE.md` and update any linked `blocked by` or `blocks` references in remaining tasks.
