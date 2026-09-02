$ErrorActionPreference = "Stop"

$root = Join-Path (Get-Location) "Statbook-Data-Architecture"

Write-Host "Creating folder structure at: $root" -ForegroundColor Cyan

# Create directories  
$dirs = @(  
    "01_Product-and-Planning",  
    "02_Architecture-and-Stack",  
    "03_Codex-Build-System",  
    "04_Deployment-and-Ops",  
    "05_Prototype-Assets\logos",  
    "05_Prototype-Assets\screenshots",  
    "05_Prototype-Assets\brand",  
    "05_Prototype-Assets\notes",  
    "06_Static-Prototype\current",  
    "06_Static-Prototype\review-snapshots\v1",  
    "06_Static-Prototype\review-snapshots\v2",  
    "06_Static-Prototype\review-snapshots\stakeholder-review",  
    "06_Static-Prototype\notes",  
    "90_Exports\word",  
    "90_Exports\pdf",  
    "99_Archive\superseded-docs",  
    "99_Archive\old-prompts",  
    "99_Archive\draft-fragments",  
    "99_Archive\deprecated-exports"  
)

foreach ($dir in $dirs) {  
    New-Item -ItemType Directory -Force -Path (Join-Path $root $dir) | Out-Null  
}

Write-Host "Creating files..." -ForegroundColor Cyan

$files = @(  
    "00_README-Statbook-Data-Architecture.md",  
    "00_Document-Priority-and-Source-of-Truth.md",  
    "00_Prototype-Scope-and-UX-Goals.md",  
    "00_Brand-and-Visual-Reference.md",  
    "00_Prototype-Asset-Usage-Guide.md",  
    "00_Prototype-Review-Checklist.md",  
    "23_Static-Prototype-Prompt-Master.md",  
    "24_Static-Prototype-File-Map.md",  
    "25_Static-Prototype-Sample-Content.md",  
    "26_Static-Prototype-Acceptance-Criteria.md",

    "01_Product-and-Planning\01_Technical-Blueprint.md",  
    "01_Product-and-Planning\02_Implementation-Backlog.md",  
    "01_Product-and-Planning\03_Database-Schema-Draft.md",  
    "01_Product-and-Planning\04_API-Endpoints-Plan.md",

    "02_Architecture-and-Stack\10_Laravel-Starter-Architecture.md",  
    "02_Architecture-and-Stack\11_Service-Skeletons.md",  
    "02_Architecture-and-Stack\12_Laravel-File-Stubs.md",

    "03_Codex-Build-System\20_Codex-Oriented-Architecture-and-Build-Guide.md",  
    "03_Codex-Build-System\21_Phase-1-2-Codex-Starter-Pack.md",  
    "03_Codex-Build-System\22_Codex-Prompt-Master.md",

    "04_Deployment-and-Ops\30_Laravel-Deployment-Checklist.md",

    "05_Prototype-Assets\brand\color-palette-reference.md",  
    "05_Prototype-Assets\brand\typography-reference.md",  
    "05_Prototype-Assets\brand\logo-usage-notes.md",  
    "05_Prototype-Assets\notes\prototype-asset-notes.md",

    "06_Static-Prototype\current\index.html",  
    "06_Static-Prototype\current\dashboard.html",  
    "06_Static-Prototype\current\teams.html",  
    "06_Static-Prototype\current\team-detail.html",  
    "06_Static-Prototype\current\create-team.html",  
    "06_Static-Prototype\current\player-identity.html",  
    "06_Static-Prototype\current\guardian-relationships.html",  
    "06_Static-Prototype\current\styles.css",  
    "06_Static-Prototype\current\app.js",  
    "06_Static-Prototype\current\README.md",

    "06_Static-Prototype\notes\prototype-feedback.md",  
    "06_Static-Prototype\notes\refinement-notes.md",  
    "06_Static-Prototype\notes\decisions-log.md"  
)

foreach ($file in $files) {  
    $path = Join-Path $root $file  
    New-Item -ItemType File -Force -Path $path | Out-Null  
}

Write-Host "Writing starter file contents..." -ForegroundColor Cyan

@"  
# Bulldog Statbook Static Prototype

This folder contains the current active static prototype for Bulldog Statbook.

Purpose:  
- desktop-first UX evaluation  
- visual review  
- brand alignment with bulldogstats.com

Deployment:  
- static HTML/CSS/JS  
- suitable for Cloudflare Pages

Primary files:  
- index.html  
- dashboard.html  
- teams.html  
- team-detail.html  
- create-team.html  
- player-identity.html  
- guardian-relationships.html  
- styles.css  
- app.js  
"@ | Set-Content -Path (Join-Path $root "06_Static-Prototype\current\README.md")

@"  
# Prototype Asset Notes

Use this folder for approved Bulldog prototype assets.

Recommended contents:  
- logos/  
- screenshots/  
- brand/  
- notes/

Notes:  
- Prefer real Bulldog logo assets over placeholders  
- Use bulldogstats.com screenshots as visual reference only  
- Do not use fake mascot graphics or clip-art  
"@ | Set-Content -Path (Join-Path $root "05_Prototype-Assets\notes\prototype-asset-notes.md")

@"  
# Prototype Feedback

Use this file to capture review notes from prototype walkthroughs.

Suggested sections:  
- What feels strong  
- What feels too generic  
- What feels off-brand  
- Page-specific issues  
- Next refinement priorities  
"@ | Set-Content -Path (Join-Path $root "06_Static-Prototype\notes\prototype-feedback.md")

Write-Host ""  
Write-Host "Done. Folder structure created successfully." -ForegroundColor Green  
Write-Host ""  
Write-Host "Location:" -ForegroundColor Yellow  
Write-Host $root  
Write-Host ""  
Write-Host "To view the top-level contents, run:" -ForegroundColor Yellow  
Write-Host 'Get-ChildItem ".\Statbook-Data-Architecture"'  