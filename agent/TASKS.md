# TASKS.md

## Purpose

This file keeps the active working set small enough to remain in normal agent context. Move completed tasks to `agent/TASK_ARCHIVE.md` and move lower-priority or farther-future work to `agent/TASK_BACKLOG.md`.

## Active Tasks

### TASK-078

- Name: Audit CSS ownership and move styles to their closest appropriate home
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Review the existing frontend SCSS files and fix ownership issues by moving duplicated cross-cutting rules upward for reuse and moving page- or component-specific rules downward into the closest logical stylesheet.

## Task Management Rules

- Keep this file limited to the current working set.
- Prefer moving future work to `agent/TASK_BACKLOG.md` instead of growing this file indefinitely.
- When a task is completed, move it to `agent/TASK_ARCHIVE.md` and update any linked `blocked by` or `blocks` references in remaining tasks.
