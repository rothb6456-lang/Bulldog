$ErrorActionPreference = "Stop"

$root = Get-Location

Write-Host "Using root folder:" -ForegroundColor Cyan  
Write-Host $root  
Write-Host ""

function Move-IfExists {  
    param (  
        [string]$SourceRelativePath,  
        [string]$DestinationRelativePath  
    )

    $source = Join-Path $root $SourceRelativePath  
    $destination = Join-Path $root $DestinationRelativePath

    if (Test-Path $source) {  
        $destFolder = Split-Path $destination -Parent  
        if (-not (Test-Path $destFolder)) {  
            New-Item -ItemType Directory -Force -Path $destFolder | Out-Null  
        }

        Move-Item -Force $source $destination  
        Write-Host "Moved: $SourceRelativePath -> $DestinationRelativePath" -ForegroundColor Green  
    }  
    else {  
        Write-Host "Skipped (not found): $SourceRelativePath" -ForegroundColor Yellow  
    }  
}

Move-IfExists "22_Codex-Prompt-Master.md" "03_Codex-Build-System\22_Codex-Prompt-Master.md"

Move-IfExists "23_Static-Prototype-Prompt-Master-Prompts 1234.txt" "99_Archive\draft-fragments\23_Static-Prototype-Prompt-Master-Prompts 1234.txt"  
Move-IfExists "24_Static-Prototype-File-Map.txt" "99_Archive\draft-fragments\24_Static-Prototype-File-Map.txt"  
Move-IfExists "25_Static-Prototype-Sample-Content.txt" "99_Archive\draft-fragments\25_Static-Prototype-Sample-Content.txt"  
Move-IfExists "26_Static-Prototype-Acceptance-Criteria.txt" "99_Archive\draft-fragments\26_Static-Prototype-Acceptance-Criteria.txt"  
Move-IfExists "myscript_foldercleanup.txt" "99_Archive\draft-fragments\myscript_foldercleanup.txt"

Move-IfExists "Use this after Codex generates the first static prototype.md" "99_Archive\old-prompts\Use this after Codex generates the first static prototype.md"

Write-Host ""  
Write-Host "Cleanup pass complete." -ForegroundColor Cyan  
Write-Host ""  
Write-Host "Check the root with:" -ForegroundColor Yellow  
Write-Host 'Get-ChildItem'  