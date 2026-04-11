# TASKS.md

## Purpose

This file keeps the active working set small enough to remain in normal agent context. Move completed tasks to `agent/TASK_ARCHIVE.md` and move lower-priority or farther-future work to `agent/TASK_BACKLOG.md`.

## Active Tasks

### TASK-091

- Name: Implement transcript memory-management workflow
- Priority: High
- Blocked By: None
- Blocks: TASK-092, TASK-093
- Description: Add a low-token transcript maintenance strategy, likely based on helper scripts and/or transcript partitioning, so agents can identify the correct append target and update transcript state without repeatedly loading the full conversation history.

## Task Management Rules

- Keep this file limited to the current working set.
- Prefer moving future work to `agent/TASK_BACKLOG.md` instead of growing this file indefinitely.
- When a task is completed, move it to `agent/TASK_ARCHIVE.md` and update any linked `blocked by` or `blocks` references in remaining tasks.
