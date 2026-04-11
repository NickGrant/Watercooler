# AGENT_DOC_AUDIT.md

## Purpose

This file captures the findings from `TASK-083` so follow-up cleanup work can resolve concrete issues instead of relying on broad "improve docs" direction.

## Scope Reviewed

- `AGENTS.md`
- `agent/AGENT_WORKFLOW.md`
- `agent/LLM_CONTEXT.md`
- `agent/TASKS.md`
- `agent/TASK_BACKLOG.md`
- `agent/TASK_ARCHIVE.md`

## Findings

### 1. Source-of-truth boundaries are mostly good but still have a few duplicated rule zones

- `AGENTS.md` and `agent/LLM_CONTEXT.md` both describe documentation placement and file ownership.
- `AGENTS.md` and `agent/AGENT_WORKFLOW.md` both carry workflow-adjacent guidance such as transcript-before-commit behavior.
- This is not a severe contradiction today, but it increases drift risk when rules evolve.

### 2. Feature execution behavior is under-specified in the persistent docs

- The repo now follows a stronger feature pattern than the docs currently state.
- The current docs do not yet clearly encode that feature work should evaluate parallelism up front, complete tasks with commit-per-task discipline, notify the user at feature completion, and pause only when missing information creates real risk.

### 3. Cross-file references can reduce maintenance burden further

- The recent `Never Load` and search-boundary cleanup improved this pattern.
- Similar opportunities still exist where one file should point to another file's canonical rule set instead of repeating a near-copy.
- The main remaining candidates are documentation/file-placement guidance and workflow ownership.

### 4. Task tracker files are clear but lightly repetitive

- `agent/TASKS.md`, `agent/TASK_BACKLOG.md`, and `agent/TASK_ARCHIVE.md` each repeat similar structural guidance.
- The duplication is small and not immediately harmful, so this is lower priority than the workflow and ownership cleanups above.

### 5. No major contradictory rule was found in the active agent docs

- The current issue is maintainability and clarity, not conflicting instructions.
- Most of the useful cleanup is consolidation and sharper ownership, not policy reversal.

## Recommended Follow-Up

### TASK-084

- tighten ownership so `AGENTS.md` stays focused on high-level repo guardrails
- keep `agent/LLM_CONTEXT.md` as the main source for context-loading and documentation-routing rules
- keep `agent/AGENT_WORKFLOW.md` as the main source for operational behavior
- remove or reduce duplicated wording where one file can reference another cleanly

### TASK-085

- add an explicit feature execution workflow rule set
- make the expected parallel-evaluation and commit-per-task behavior persistent instead of conversational
