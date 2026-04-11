# STEPS.md

## Purpose

This file keeps the active working set small enough to remain in normal agent context. Move completed steps to `agent/STEP_ARCHIVE.md` and move lower-priority or farther-future work to `agent/STEP_BACKLOG.md`.

## Active Steps

### STEP-109

- Name: Fix resource selection UX and recoverable action messaging
- Priority: High
- Blocked By: None
- Blocks: STEP-110, STEP-111
- Description: Rework take-resource selection to support duplicate picks cleanly, surface recoverable conflicts as actionable errors instead of generic resync loops, and add Executive Favor spend confirmation before purchases.


## Step Management Rules

- Keep this file limited to the current working set.
- Prefer moving future work to `agent/STEP_BACKLOG.md` instead of growing this file indefinitely.
- When a step is completed, move it to `agent/STEP_ARCHIVE.md` and update any linked `blocked by` or `blocks` references in remaining steps.
