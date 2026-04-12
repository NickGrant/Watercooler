# STEPS.md

## Purpose

This file keeps the active working set small enough to remain in normal agent context. Move completed steps to `agent/STEP_ARCHIVE.md` and move lower-priority or farther-future work to `agent/STEP_BACKLOG.md`.

## Active Steps

### STEP-112

- Name: Generate full avatar composite review set
- Priority: High
- Blocked By: None
- Blocks: STEP-113, STEP-114
- Description: Build a repeatable script that overlays the normalized hair, face, and body assets into full composite renders for every unique supported combination so the avatar library can be reviewed at scale.

### STEP-114

- Name: Automate repeated avatar corrections and verify results
- Priority: High
- Blocked By: None
- Blocks: None
- Description: For avatar issues that are script-fixable and appear across at least three generated composites, build correction scripts, iterate fixes and regenerated outputs up to ten loops, and report which composites or assets were successfully improved.


## Step Management Rules

- Keep this file limited to the current working set.
- Prefer moving future work to `agent/STEP_BACKLOG.md` instead of growing this file indefinitely.
- When a step is completed, move it to `agent/STEP_ARCHIVE.md` and update any linked `blocked by` or `blocks` references in remaining steps.
