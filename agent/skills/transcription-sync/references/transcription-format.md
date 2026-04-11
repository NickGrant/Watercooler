# Transcription Format

## Goal

Keep `agent/TRANSCRIPTION.md` append-only, readable, and easy to continue from the last recorded point.

## Expected Structure

- `# TRANSCRIPTION.md`
- `## Purpose`
- `## Format Rules`
- `## Transcript`
- one or more `### Session NNN` sections
- one or more `#### Entry NNN` sections inside each session
- `User:` block
- `Assistant:` block
- `## Sync Notes`

## Update Rules

- Append new entries instead of rewriting the file.
- Continue numbering from the last visible entry.
- Start a new session only when the repository has a clear reason to distinguish sessions.
- Keep links and file paths in plain markdown form if they appeared in the original message.
- Leave tool output out of the transcript unless the user explicitly asks to include it.

## Preferred Behavior

- Favor accuracy over compression.
- If a long assistant turn contained many progress updates, summarize those updates into the final practical outcome unless the transcript is specifically meant to preserve every intermediate message.
- If uncertain whether a turn was already transcribed, compare the latest entry text before appending.
- Keep the transcript portable across common markdown viewers; avoid transcript formatting that depends on custom CSS, right-aligned HTML layout, or colored message containers inside the markdown file itself.
