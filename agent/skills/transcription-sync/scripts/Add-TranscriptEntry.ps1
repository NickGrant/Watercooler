param(
    [string]$RepoRoot,
    [string]$TargetPath,
    [Parameter(Mandatory = $true)][string]$Session,
    [Parameter(Mandatory = $true)][string]$Entry,
    [string]$UserMessage = '',
    [string]$AssistantMessage = '',
    [string]$Purpose = 'This transcript file records user-assistant conversation for the Watercooler project.'
)

if ([string]::IsNullOrWhiteSpace($RepoRoot)) {
    $RepoRoot = (Resolve-Path (Join-Path $PWD '..')).Path
    if (-not (Test-Path (Join-Path $RepoRoot 'agent'))) {
        $RepoRoot = (Get-Location).Path
    }
}

if ([string]::IsNullOrWhiteSpace($TargetPath)) {
    $agentDir = Join-Path $RepoRoot 'agent'
    $continuationPath = Join-Path $agentDir 'TRANSCRIPTION_2.md'
    $primaryPath = Join-Path $agentDir 'TRANSCRIPTION.md'
    $TargetPath = if (Test-Path $continuationPath) { $continuationPath } else { $primaryPath }
}

if (-not (Test-Path $TargetPath)) {
    $header = @(
        "# $(Split-Path $TargetPath -Leaf)",
        '',
        '## Purpose',
        '',
        $Purpose,
        '',
        '## Format Rules',
        '',
        '- Append new entries in chronological order.',
        '- Clearly label each speaker as `**User**` or `**Assistant**`.',
        '- Record visible conversation messages only.',
        '- Do not include tool outputs unless the user specifically asks for them to be transcribed.',
        '- Do not rewrite earlier entries unless correcting a clear transcription mistake.',
        '- Keep `## Sync Notes` updated with the latest recorded session and entry.',
        '- Use a simple `---` divider between entries for easier scanning.',
        '',
        '## Transcript',
        '',
        "### Session $Session",
        '',
        '## Sync Notes',
        '',
        "- Latest recorded session: ``$Session``",
        "- Latest recorded entry: ``0``"
    )
    $header -join "`r`n" | Set-Content $TargetPath
}

$content = Get-Content $TargetPath -Raw
$sessionHeader = "### Session $Session"

$entryLines = @(
    "#### Entry $Entry"
)

if (-not [string]::IsNullOrWhiteSpace($UserMessage)) {
    $entryLines += '**User**'
    $entryLines += $UserMessage.TrimEnd()
    $entryLines += ''
}

if (-not [string]::IsNullOrWhiteSpace($AssistantMessage)) {
    $entryLines += '**Assistant**'
    $entryLines += $AssistantMessage.TrimEnd()
    $entryLines += ''
}

$entryLines += '---'

$entryBlock = ($entryLines -join "`r`n").TrimEnd()
$body = $content -replace "(?ms)\r?\n## Sync Notes.*$", ''

if ($body -notmatch [regex]::Escape($sessionHeader)) {
    $body = $body.TrimEnd() + "`r`n`r`n$sessionHeader`r`n"
}

$body = $body.TrimEnd() + "`r`n`r`n$entryBlock`r`n`r`n"
$syncNotes = @(
    '## Sync Notes',
    '',
    "- Latest recorded session: ``$Session``",
    "- Latest recorded entry: ``$Entry``"
) -join "`r`n"

$updated = $body.TrimEnd() + "`r`n`r`n$syncNotes`r`n"
Set-Content $TargetPath $updated
