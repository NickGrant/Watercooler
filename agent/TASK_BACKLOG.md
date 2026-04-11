# TASK_BACKLOG.md

## Purpose

This file tracks outstanding work that matters to the project but does not need to stay in the default context window.

## Backlog Tasks

### TASK-091

- Name: Implement transcript memory-management workflow
- Priority: High
- Blocked By: TASK-089
- Blocks: TASK-092, TASK-093
- Description: Add a low-token transcript maintenance strategy, likely based on helper scripts and/or transcript partitioning, so agents can identify the correct append target and update transcript state without repeatedly loading the full conversation history.

### TASK-092

- Name: Improve transcript readability within portable markdown limits
- Priority: Medium
- Blocked By: TASK-089, TASK-090, TASK-091
- Blocks: None
- Description: Improve at-a-glance readability in the transcript format using portable markdown conventions such as stronger speaker labels and separators, while avoiding viewer-specific HTML or CSS dependencies.

### TASK-090

- Name: Move transcription rules into the transcription skill and make transcript creation resilient
- Priority: High
- Blocked By: TASK-089
- Blocks: TASK-092
- Description: Move as many transcription-maintenance rules as practical into the shared `transcription-sync` skill, reduce the skill's dependency on other repo docs where possible, and ensure the workflow can create the target transcript file if it is missing.

### TASK-093

- Name: Rename TASK and FEATURE workflow to STEP and JOB
- Priority: High
- Blocked By: TASK-091
- Blocks: TASK-094
- Description: Rename the agent tracker vocabulary and file set from TASK/FEATURE to STEP/JOB across the repo, updating the existing files, references, and source-of-truth docs to match the new terminology.

### TASK-094

- Name: Encapsulate step and job workflow into portable skills
- Priority: Medium
- Blocked By: TASK-093
- Blocks: None
- Description: Package the repo's step/job maintenance workflow into portable shared skill definitions so the operating pattern is less tied to one specific agent adapter or one-off doc wording.

### TASK-095

- Name: Remove preview game route entry from home page
- Priority: Medium
- Blocked By: None
- Blocks: TASK-096, TASK-097
- Description: Remove the preview-game-route shortcut from the landing page so the create-game path remains the primary entry into the app.

### TASK-096

- Name: Add collapsible rules section to the home page
- Priority: Medium
- Blocked By: TASK-095
- Blocks: None
- Description: Add a collapsible rules/help surface on the front page so players can review the basic gameplay loop before entering a room.

### TASK-097

- Name: Rework check-in and waiting-room layout into two columns
- Priority: High
- Blocked By: TASK-095
- Blocks: None
- Description: Rebuild the pre-game game-page layout into a 50/50 two-column split with check-in on one side and the waiting room on the other, removing the room snapshot panel entirely.

### TASK-079

- Name: Add bug report persistence schema and storage contract
- Priority: High
- Blocked By: None
- Blocks: TASK-080, TASK-081, TASK-082
- Description: Add the database table and backend repository contract for in-game bug reports, storing unread/read status, message content, optional reply email, created timestamp, reporter display name, room slug, and lightweight debugging snapshot fields that are not reliably recoverable later.

### TASK-080

- Name: Implement bug report submission API flow
- Priority: High
- Blocked By: TASK-079
- Blocks: TASK-081, TASK-082
- Description: Add a server-side submission endpoint and service that validates required message content, accepts an optional reply email, resolves the active player and room context, and persists the report in unread state for later DB-side review.

### TASK-081

- Name: Build floating in-game bug report panel
- Priority: High
- Blocked By: TASK-080
- Blocks: TASK-082
- Description: Add a right-edge floating bug-report tab on the game page that expands into a compact form with optional reply email, required message field, submission feedback, and only the minimum additional client context needed for debugging.

### TASK-082

- Name: Cover bug report flow with automated tests and docs
- Priority: Medium
- Blocked By: TASK-079, TASK-080, TASK-081
- Blocks: None
- Description: Add backend and frontend coverage for bug report submission behavior and update any planning or setup docs needed to reflect the new persistence path and operating assumptions.
