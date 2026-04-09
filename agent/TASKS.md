# TASKS.md

## Purpose

This file keeps the active working set small enough to remain in normal agent context. Move completed tasks to `agent/TASK_ARCHIVE.md` and move lower-priority or farther-future work to `agent/TASK_BACKLOG.md`.

## Active Tasks

### TASK-038

- Name: Capture and triage active UAT findings
- Priority: High
- Blocked By: None
- Blocks: TASK-039, TASK-046
- Description: Record incoming UAT issues, confirm expected behavior, reproduce defects, and turn validated findings into implementation tasks.

### TASK-050

- Name: Replace resource text labels with compact iconography
- Priority: Medium
- Blocked By: TASK-038
- Blocks: None
- Description: Replace or supplement verbose resource labels with recognizable iconography so the resource displays become significantly tighter and faster to scan.

### TASK-051

- Name: Redesign executive cards with a Guess Who style visual treatment
- Priority: Medium
- Blocked By: TASK-038
- Blocks: None
- Description: Redesign the executive cards to feel more visual and character-forward, with a presentation reminiscent of Guess Who style portrait cards.

### TASK-052

- Name: Expand the game scene to use more of the available viewport
- Priority: High
- Blocked By: TASK-038
- Blocks: None
- Description: Adjust the game screen layout so it uses more of the available horizontal space and brings more of the primary board state above the fold.

## Task Management Rules

- Keep this file limited to the current working set.
- Prefer moving future work to `agent/TASK_BACKLOG.md` instead of growing this file indefinitely.
- When a task is completed, move it to `agent/TASK_ARCHIVE.md` and update any linked `blocked by` or `blocks` references in remaining tasks.
