# STEPS.md

## Purpose

This file keeps the active working set small enough to remain in normal agent context. Move completed steps to `agent/STEP_ARCHIVE.md` and move lower-priority or farther-future work to `agent/STEP_BACKLOG.md`.

## Active Steps

### STEP-104

- Name: Remove obsolete realtime deployment surface
- Priority: High
- Blocked By: STEP-103
- Blocks: None
- Description: Remove or archive repo assets that imply a required standalone realtime service when they are no longer part of the active deployment path, including stale docker/runtime references and now-unused backend realtime scaffolding if nothing live depends on it.

## Step Management Rules

- Keep this file limited to the current working set.
- Prefer moving future work to `agent/STEP_BACKLOG.md` instead of growing this file indefinitely.
- When a step is completed, move it to `agent/STEP_ARCHIVE.md` and update any linked `blocked by` or `blocks` references in remaining steps.
