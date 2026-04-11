---
name: transcription-sync
description: Update the active repository transcript target with the latest visible user and assistant conversation turns. Use when preparing a commit, before ending a session or clearing context, or whenever the repository transcript may be behind the current conversation.
---

# Transcription Sync

## Overview

This is the shared, system-agnostic definition for the `transcription-sync` skill.

Use this skill to keep the active repository transcript current without rewriting the full conversation log. Append only the missing turns after the latest recorded entry, preserve the repository transcript format, and rely on the helper scripts to keep transcript maintenance low-cost.

## Workflow

1. Run `agent/skills/transcription-sync/scripts/Get-TranscriptState.ps1` to identify the active transcript target and the latest recorded session and entry.
2. Load only the active transcript target if direct inspection is still needed.
3. Identify the visible user and assistant messages that happened after the latest recorded entry.
4. Append only the missing turns in chronological order.
5. Use `agent/skills/transcription-sync/scripts/Add-TranscriptEntry.ps1` when it helps create or update the active transcript target without manually rewriting the whole file.
6. Keep `## Sync Notes` accurate with the latest recorded session and entry.
7. Save the active transcript target before any commit is created.

## Recording Rules

- Label each block as `User:` or `Assistant:`.
- Record the message content in a readable form.
- Preserve user intent accurately.
- Preserve assistant outcomes accurately.
- Omit tool outputs, shell output, and hidden reasoning.
- Do not alter older entries unless fixing a clear transcription error.
- If the latest assistant response has not been sent yet, do not invent it.
- Keep the transcript portable plain markdown rather than relying on HTML/CSS-only presentation tricks.

## Granularity Guidance

- Prefer exact or near-exact wording for user prompts.
- For assistant replies, preserve the practical meaning and outcomes even if a concise summary is more readable than every intermediate status update.
- Keep one transcript entry per user turn unless the repository format clearly calls for something else.

## System Notes

- System-specific skill directories should keep thin pointer files that refer back to this shared definition.
- Tool-specific adapter files may add required metadata, but the actual workflow should stay here whenever possible.
- During transcript migrations, the active target may be a continuation file such as `agent/TRANSCRIPTION_2.md` instead of the historical archive file.
- If the active transcript target does not exist yet, create it with the expected transcript structure before appending entries.

## Reference

Read `agent/skills/transcription-sync/references/transcription-format.md` before making structural changes to the transcript format.
Read `agent/skills/transcription-sync/references/transcript-memory-management.md` before changing the low-token transcript workflow.
