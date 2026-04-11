---
name: step-job-sync
description: Maintain Watercooler's step and job tracking files. Use when step/job status changes, when adding planned work, or when closing a job after its steps are complete.
---

# Step Job Sync

## Overview

This is the shared, system-agnostic definition for the `step-job-sync` skill.

Use this skill whenever tracked work changes shape. It keeps `agent/STEPS.md`, `agent/STEP_BACKLOG.md`, `agent/STEP_ARCHIVE.md`, and `resources/planning/JOBS.md` aligned without letting one file drift away from the others.

## Workflow

1. Load only the tracker files needed for the current change:
   - `agent/STEPS.md`
   - `agent/STEP_BACKLOG.md`
   - `agent/STEP_ARCHIVE.md`
   - `resources/planning/JOBS.md`
2. If a step is starting now, keep it in `agent/STEPS.md` and keep the active working set small.
3. If a step is future work, keep it in `agent/STEP_BACKLOG.md`.
4. If a step is complete, move it to `agent/STEP_ARCHIVE.md`.
5. Keep `resources/planning/JOBS.md` aligned with the current step list and update the job status when work moves from `Not Started` to `In Progress` to `Complete`.
6. If new work is discovered, add the next sequential `STEP-###` and `JOB-###` identifiers and map them immediately.
7. If a blocker or dependency changes, update `Blocked By` and `Blocks` references in the remaining tracker entries.
8. Before a commit, make sure the tracker files describe the actual repo state rather than an intended future state.

## Tracking Rules

- Use `STEP-###` identifiers for tracked work items and `JOB-###` identifiers for grouped workstreams.
- Keep each entry concise and action-oriented.
- Each step entry should include:
  - `Name`
  - `Priority`
  - `Blocked By`
  - `Blocks`
  - `Description`
- Keep `agent/STEPS.md` focused on the current working set rather than the whole roadmap.
- Do not leave completed work in `agent/STEPS.md` or `agent/STEP_BACKLOG.md`.
- Do not mark a job `Complete` while any of its mapped steps are still active or pending.

## Execution Rules

- Evaluate a job for safe parallel work before implementation begins.
- Use parallel lanes only when they do not create avoidable overlap or merge risk.
- Commit each completed step separately after validating its scope.
- Notify the user when the full job is complete, then wait for more instructions.
- Stop and ask for information whenever required context is missing or a risky assumption would otherwise be necessary.

## Reference

Read `agent/skills/step-job-sync/references/step-job-files.md` before changing tracker structure or adding new workflow files.
