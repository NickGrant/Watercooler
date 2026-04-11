# TASKS.md

## Purpose

This file keeps the active working set small enough to remain in normal agent context. Move completed tasks to `agent/TASK_ARCHIVE.md` and move lower-priority or farther-future work to `agent/TASK_BACKLOG.md`.

## Active Tasks

### TASK-087

- Name: Expand pre-commit doc maintenance rule
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Update the commit workflow guidance so agents review and update `README.md`, `AGENTS.md`, and `agent/LLM_CONTEXT.md` whenever a change should affect those source-of-truth docs, keeping the core repo guidance continuously current instead of letting drift accumulate.

## Task Management Rules

- Keep this file limited to the current working set.
- Prefer moving future work to `agent/TASK_BACKLOG.md` instead of growing this file indefinitely.
- When a task is completed, move it to `agent/TASK_ARCHIVE.md` and update any linked `blocked by` or `blocks` references in remaining tasks.
