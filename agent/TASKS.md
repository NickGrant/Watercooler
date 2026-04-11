# TASKS.md

## Purpose

This file keeps the active working set small enough to remain in normal agent context. Move completed tasks to `agent/TASK_ARCHIVE.md` and move lower-priority or farther-future work to `agent/TASK_BACKLOG.md`.

## Active Tasks

### TASK-090

- Name: Move transcription rules into the transcription skill and make transcript creation resilient
- Priority: High
- Blocked By: None
- Blocks: TASK-092
- Description: Move as many transcription-maintenance rules as practical into the shared `transcription-sync` skill, reduce the skill's dependency on other repo docs where possible, and ensure the workflow can create the target transcript file if it is missing.

## Task Management Rules

- Keep this file limited to the current working set.
- Prefer moving future work to `agent/TASK_BACKLOG.md` instead of growing this file indefinitely.
- When a task is completed, move it to `agent/TASK_ARCHIVE.md` and update any linked `blocked by` or `blocks` references in remaining tasks.
