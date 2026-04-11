# Transcript Memory Management

## Goal

Keep transcript maintenance from requiring a full reread of the historical conversation log every time a commit is prepared.

## Current Strategy

- Treat `agent/TRANSCRIPTION.md` as the historical archive unless a migration requires otherwise.
- When `agent/TRANSCRIPTION_2.md` exists, treat it as the active continuation file for new entries.
- Prefer reading transcript state through helper scripts instead of loading the full transcript by default.

## Helper Scripts

- `agent/skills/transcription-sync/scripts/Get-TranscriptState.ps1`
  Returns the active transcript target, whether it exists, and the latest recorded session and entry.
- `agent/skills/transcription-sync/scripts/Add-TranscriptEntry.ps1`
  Appends an entry to the active transcript target and updates `## Sync Notes`. It can also create the target transcript file if it is missing.

## Recommended Low-Token Workflow

1. Run `Get-TranscriptState.ps1` to identify the active transcript file and the latest recorded entry.
2. Load only the active transcript target when a manual review is still needed.
3. Append the missing turn to the active transcript target.
4. Update sync notes rather than rescanning the full historical archive.

## Future Direction

- After the temporary continuation phase ends, the same pattern can support either a single active transcript file or a partitioned multi-file transcript archive.
- If transcript size continues to grow materially, prefer an archival split plus a small active transcript file over repeatedly loading the full historical log.
