# STEPS.md

## Purpose

This file keeps the active working set small enough to remain in normal agent context. Move completed steps to `agent/STEP_ARCHIVE.md` and move lower-priority or farther-future work to `agent/STEP_BACKLOG.md`.

## Active Steps

### STEP-105

- Name: Reconcile transport naming and deployment guidance
- Priority: Medium
- Blocked By: STEP-104
- Blocks: None
- Description: Review remaining API/frontend naming such as `realtime` transport metadata and update docs or contracts where needed so deployment and maintenance guidance matches the simplified polling-based architecture without misleading future cleanup or hosting decisions.

## Step Management Rules

- Keep this file limited to the current working set.
- Prefer moving future work to `agent/STEP_BACKLOG.md` instead of growing this file indefinitely.
- When a step is completed, move it to `agent/STEP_ARCHIVE.md` and update any linked `blocked by` or `blocks` references in remaining steps.
