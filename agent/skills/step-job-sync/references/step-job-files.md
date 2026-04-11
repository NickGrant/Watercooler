# Step And Job Files

## Purpose

This reference explains the ownership boundaries for Watercooler's tracked-work files.

## Files

- `agent/STEPS.md`
  Active working set only.
- `agent/STEP_BACKLOG.md`
  Planned work that matters but does not need to stay in the default context window.
- `agent/STEP_ARCHIVE.md`
  Completed step history.
- `resources/planning/JOBS.md`
  Job-to-step map for broader planning context.

## Maintenance Notes

- Prefer moving future work to the backlog instead of growing `agent/STEPS.md`.
- Prefer moving completed work to the archive instead of leaving it in place with a completed label.
- Keep the active step list small enough to remain useful in default context.
- Update the job map whenever new steps are created, moved, or completed.
- Keep file responsibilities narrow so shared workflow logic can stay portable across agent systems.
