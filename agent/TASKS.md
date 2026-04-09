# TASKS.md

## Purpose

This file keeps the active working set small enough to remain in normal agent context. Move completed tasks to `agent/TASK_ARCHIVE.md` and move lower-priority or farther-future work to `agent/TASK_BACKLOG.md`.

## Active Tasks

### TASK-038

- Name: Capture and triage active UAT findings
- Priority: High
- Blocked By: None
- Blocks: TASK-039, TASK-044, TASK-046
- Description: Record incoming UAT issues, confirm expected behavior, reproduce defects, and turn validated findings into implementation tasks.

### TASK-044

- Name: Rework avatar picker order and interaction model
- Priority: Medium
- Blocked By: TASK-038
- Blocks: TASK-045
- Description: Change the avatar builder to present options in hair, face, body order and replace the current selectors with image carousel controls that preview each choice clearly.

### TASK-045

- Name: Create avatar option art assets
- Priority: Medium
- Blocked By: TASK-044
- Blocks: None
- Description: Produce or integrate the graphic assets needed to represent each avatar choice in the carousel-based builder.

## Task Management Rules

- Keep this file limited to the current working set.
- Prefer moving future work to `agent/TASK_BACKLOG.md` instead of growing this file indefinitely.
- When a task is completed, move it to `agent/TASK_ARCHIVE.md` and update any linked `blocked by` or `blocks` references in remaining tasks.
