# STEPS.md

## Purpose

This file keeps the active working set small enough to remain in normal agent context. Move completed steps to `agent/STEP_ARCHIVE.md` and move lower-priority or farther-future work to `agent/STEP_BACKLOG.md`.

## Active Steps

### STEP-107

- Name: Render layered PNG avatars in the frontend
- Priority: High
- Blocked By: STEP-106
- Blocks: STEP-108
- Description: Replace the old single-layer SVG avatar setup with a reusable layered PNG avatar component in the join flow and lobby surfaces so avatars compose and scale predictably.


## Step Management Rules

- Keep this file limited to the current working set.
- Prefer moving future work to `agent/STEP_BACKLOG.md` instead of growing this file indefinitely.
- When a step is completed, move it to `agent/STEP_ARCHIVE.md` and update any linked `blocked by` or `blocks` references in remaining steps.
