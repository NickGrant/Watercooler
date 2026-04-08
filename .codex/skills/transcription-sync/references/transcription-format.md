# Transcription Format

Use the same transcript structure described in the repository's shared workflow and transcript file.

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
- Start a new session only when there is a clear reason to separate it from the previous work.
- Keep links and file paths in markdown form if they appeared in the original message.
- Leave tool output out of the transcript unless the user explicitly asks to include it.
