---
name: transcription-sync
description: Update agent/TRANSCRIPTION.md with the latest visible user and assistant conversation turns. Use when preparing a commit, before ending a session or clearing context, or whenever the repository transcript may be behind the current conversation.
---

# Transcription Sync

## Overview

Use this skill to keep `agent/TRANSCRIPTION.md` current without rewriting the full conversation log. Append only the missing turns after the latest recorded entry and preserve the repository transcript format.

## Workflow

1. Open `agent/AGENT_WORKFLOW.md` for the shared transcript-maintenance rule.
2. Open `agent/TRANSCRIPTION.md`.
3. Find the latest recorded session and entry number in the `## Transcript` section.
4. Identify the visible user and assistant messages that happened after that point in the current conversation.
5. Append only the missing turns in chronological order.
6. Update the `## Sync Notes` section with the latest recorded session and entry.
7. Save `agent/TRANSCRIPTION.md` before any commit is created.

## Recording Rules

- Label each block as `User:` or `Assistant:`.
- Record the message content in a readable form.
- Preserve user intent accurately.
- Preserve assistant outcomes accurately.
- Omit tool outputs, shell output, and hidden reasoning.
- Do not alter older entries unless fixing a clear transcription error.
- If the latest assistant response has not been sent yet, do not invent it.

## Granularity Guidance

- Prefer exact or near-exact wording for user prompts.
- For assistant replies, preserve the practical meaning and outcomes even if a concise summary is more readable than every intermediate status update.
- Keep one transcript entry per user turn unless the repository format clearly calls for something else.

## Session-End Hook

This repository includes `.github/copilot-instructions.md` as a Copilot-side adapter and `agent/AGENT_WORKFLOW.md` as the shared rule source. Treat the hook as advisory, not guaranteed automation.

## Reference

Read `references/transcription-format.md` before making structural changes to the transcript format.
