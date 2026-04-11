# STEPS.md

## Purpose

This file keeps the active working set small enough to remain in normal agent context. Move completed steps to `agent/STEP_ARCHIVE.md` and move lower-priority or farther-future work to `agent/STEP_BACKLOG.md`.

## Active Steps

### STEP-080

- Name: Implement bug report submission API flow
- Priority: High
- Blocked By: None
- Blocks: STEP-081, STEP-082
- Description: Add a server-side submission endpoint and service that validates required message content, accepts an optional reply email, resolves the active player and room context, and persists the report in unread state for later DB-side review.

## Step Management Rules

- Keep this file limited to the current working set.
- Prefer moving future work to `agent/STEP_BACKLOG.md` instead of growing this file indefinitely.
- When a step is completed, move it to `agent/STEP_ARCHIVE.md` and update any linked `blocked by` or `blocks` references in remaining steps.
