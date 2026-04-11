param(
    [string]$RepoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..\..\..')).Path
)

$agentDir = Join-Path $RepoRoot 'agent'
$primaryPath = Join-Path $agentDir 'TRANSCRIPTION.md'
$continuationPath = Join-Path $agentDir 'TRANSCRIPTION_2.md'

$targetPath = if (Test-Path $continuationPath) {
    $continuationPath
} else {
    $primaryPath
}

$exists = Test-Path $targetPath
$latestSession = $null
$latestEntry = $null

if ($exists) {
    $content = Get-Content $targetPath -Raw

    if ($content -match 'Latest recorded session:\s*`(?<session>\d+)`') {
        $latestSession = $matches['session']
    } elseif ($content -match '### Session (?<session>\d+)') {
        $sessions = [regex]::Matches($content, '### Session (?<session>\d+)')
        if ($sessions.Count -gt 0) {
            $latestSession = $sessions[$sessions.Count - 1].Groups['session'].Value
        }
    }

    if ($content -match 'Latest recorded entry:\s*`(?<entry>\d+)`') {
        $latestEntry = $matches['entry']
    } elseif ($content -match '#### Entry (?<entry>\d+)') {
        $entries = [regex]::Matches($content, '#### Entry (?<entry>\d+)')
        if ($entries.Count -gt 0) {
            $latestEntry = $entries[$entries.Count - 1].Groups['entry'].Value
        }
    }
}

$result = [ordered]@{
    repoRoot = $RepoRoot
    targetPath = $targetPath
    targetFile = Split-Path $targetPath -Leaf
    exists = $exists
    latestSession = $latestSession
    latestEntry = $latestEntry
    usingContinuation = (Split-Path $targetPath -Leaf) -eq 'TRANSCRIPTION_2.md'
}

$result | ConvertTo-Json -Depth 3
