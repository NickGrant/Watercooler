# TASK_BACKLOG.md

## Purpose

This file tracks outstanding work that matters to the project but does not need to stay in the default context window.

## Backlog Tasks

### TASK-074

- Name: Expand automated coverage for major gameplay and UI flows
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Audit the current frontend and backend tests against major user-facing and rules-critical flows, then add or strengthen coverage so the main game, lobby, session recovery, and authoritative gameplay behaviors are exercised automatically.

### TASK-075

- Name: Improve inline code documentation and API comments
- Priority: Medium
- Blocked By: TASK-073
- Blocks: None
- Description: Add or tighten targeted comments, JSDoc, and PHPDoc in areas where intent, contracts, or non-obvious behavior are hard to infer, while avoiding noisy commentary on self-explanatory code.

### TASK-076

- Name: Centralize reusable visual tokens and shared CSS values
- Priority: Medium
- Blocked By: None
- Blocks: None
- Description: Audit frontend styling for repeated colors and other shared visual constants, move them into global CSS variables where appropriate, and update component styles to reuse those tokens consistently.
