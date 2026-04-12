# STEP_BACKLOG.md

## Purpose

This file tracks outstanding work that matters to the project but does not need to stay in the default context window.

## Backlog Steps

### STEP-113

- Name: Audit avatar composites and write issue report
- Priority: High
- Blocked By: STEP-112
- Blocks: STEP-114
- Description: Review the generated composite set and produce a report covering category-level avatar issues, individual asset offsets or anomalies, and bad body/face/hair combinations that need correction or manual follow-up.

### STEP-114

- Name: Automate repeated avatar corrections and verify results
- Priority: High
- Blocked By: STEP-113
- Blocks: None
- Description: For avatar issues that are script-fixable and appear across at least three generated composites, build correction scripts, iterate fixes and regenerated outputs up to ten loops, and report which composites or assets were successfully improved.

### STEP-116

- Name: Document cron-based autopurge setup for shared hosting
- Priority: Medium
- Blocked By: None
- Blocks: None
- Description: Add deployment guidance for triggering the stale-game purge every 24 hours through cPanel cron, including the command shape, expected runtime behavior, and any validation or safety notes.
