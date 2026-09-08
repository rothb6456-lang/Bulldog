<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- ⚠️ MANDATORY PRIVACY SHIELD: Strictly block search engine indexing [80, 85, 276] -->
    <meta name="robots" content="noindex, nofollow, noarchive">
    
    <title>Bulldog Statbook | Player Card - {{ $player->display_name }}</title>

    <style>
        :root {
            --bg: #f3f5f4;
            --surface: #ffffff;
            --navy: #081722;
            --navy-light: #102533;
            --green: #007a43;
            --green-soft: #e4f3eb;
            --border: #d7dede;
            --border-strong: #b9c4c4;
            --text-dark: #0d1b26;
            --text-muted: #5f6d78;
            --gold: #d4af37;
            --warning-soft: #fff2d9;
            --warning-text: #9a6700;
        }

        body {
            background-color: var(--bg);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: var(--text-dark);
        }

        /* Card Container (Sports Trading Card Styling) */
        .card-wrapper {
            background: var(--surface);
            border: 12px solid var(--navy);
            border-radius: 20px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 10px 30px rgba(8, 23, 34, 0.15);
            overflow: hidden;
            position: relative;
        }

        /* Green Accent Inner Border */
        .card-inner {
            border: 4px solid var(--green);
            border-radius: 10px;
            margin: 6px;
            background: var(--navy-light);
            display: flex;
            flex-direction: column;
            height: calc(100% - 20px);
        }

        /* Header Details */
        .card-header {
            padding: 24px 20px 15px;
            text-align: center;
            border-bottom: 2px dashed rgba(215, 222, 222, 0.2);
            background: var(--navy);
        }

        .brand-logo {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2px;
            color: var(--green);
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .player-name {
            font-size: 26px;
            font-weight: 800;
            color: #ffffff;
            margin: 0 0 4px;
            letter-spacing: -0.5px;
        }

        .team-context {
            font-size: 13px;
            font-weight: 600;
            color: var(--border);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Portrait Graphic Placeholder */
        .card-photo-box {
            background: #102d44;
            height: 220px;
            margin: 15px;
            border: 2px solid var(--green);
            border-radius: 8px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .photo-fallback {
            text-align: center;
            color: rgba(255, 255, 255, 0.4);
        }

        .photo-fallback span {
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
        }

        .jersey-badge {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: var(--green);
            color: #ffffff;
            font-weight: 900;
            font-size: 20px;
            padding: 6px 12px;
            border-radius: 6px;
            border: 2px solid var(--navy);
        }

        /* Quick-Facts Stripe */
        .facts-strip {
            display: flex;
            justify-content: space-around;
            padding: 10px 15px;
            background: var(--navy);
            border-top: 1px solid rgba(215, 222, 222, 0.1);
            border-bottom: 1px solid rgba(215, 222, 222, 0.1);
        }

        .fact-item {
            text-align: center;
        }

        .fact-label {
            font-size: 10px;
            color: var(--border);
            text-transform: uppercase;
            font-weight: 700;
        }

        .fact-value {
            font-size: 14px;
            color: #ffffff;
            font-weight: 800;
            margin-top: 2px;
        }

        /* Highlight Stat Grid */
        .stats-grid-container {
            padding: 20px;
            background: var(--surface);
            border-radius: 0 0 6px 6px;
            margin-top: auto;
        }

        .stats-title {
            font-size: 12px;
            font-weight: 800;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
            text-align: center;
        }

        .stats-card-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }

        .stat-card-box {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            text-align: center;
            padding: 10px 5px;
        }

        .stat-card-num {
            font-size: 18px;
            font-weight: 900;
            color: var(--navy);
        }

        .stat-card-lbl {
            font-size: 9px;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* ⚠️ REQUIRED DISCLOSURE STATE: Promptly state source fidelity [75, 115] */
        .disclosure-footer {
            margin-top: 15px;
            padding: 12px;
            background: var(--warning-soft);
            border: 1px solid var(--border-strong);
            border-radius: 8px;
            font-size: 10px;
            color: var(--warning-text);
            text-align: center;
            line-height: 1.4;
            max-width: 380px;
        }

        .branding-watermark {
            margin-top: 20px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="card-wrapper">
        <div class="card-inner">
            
            <!-- Branded Header -->
            <div class="card-header">
                <div class="brand-logo">Bulldog Statbook</div>
                <h1 class="player-name">{{ $player->display_name }}</h1>
                <div class="team-context">
                    {{ $player->memberships->first()->team->name ?? 'Prospect' }}
                </div>
            </div>

            <!-- Dynamic Graphic Container -->
            <div class="card-photo-box">
                <div class="photo-fallback">
                    <span>⚾</span>
                    <div>ATHLETE IDENTITY PROFILE</div>
                </div>
                <div class="jersey-badge">
                    #{{ $player->memberships->first()->jersey_number ?? '00' }}
                </div>
            </div>

            <!-- Facts Strip -->
            <div class="facts-strip">
                <div class="fact-item">
                    <div class="fact-label">Code</div>
                    <div class="fact-value" style="color: var(--gold)">{{ $player->player_code }}</div>
                </div>
                <div class="fact-item">
                    <div class="fact-label">Position</div>
                    <div class="fact-value">
                        {{ is_array($player->memberships->first()->positions_json) ? implode(', ', $player->memberships->first()->positions_json) : 'UTL' }}
                    </div>
                </div>
                <div class="fact-item">
                    <div class="fact-label">Sport</div>
                    <div class="fact-value">
                        {{ $player->memberships->first()->team->sport->name ?? 'Baseball' }}
                    </div>
                </div>
            </div>

            <!-- Bottom Stats Section -->
            <div class="stats-grid-container">
                <div class="stats-title">Career Statistics</div>
                <div class="stats-card-grid">
                    <div class="stat-card-box">
                        <div class="stat-card-num">{{ $careerStats['PA'] ?? 0 }}</div>
                        <div class="stat-card-lbl">PA</div>
                    </div>
                    <div class="stat-card-box">
                        <div class="stat-card-num">{{ $careerStats['AB'] ?? 0 }}</div>
                        <div class="stat-card-lbl">AB</div>
                    </div>
                    <div class="stat-card-box">
                        <div class="stat-card-num">{{ $careerStats['H'] ?? 0 }}</div>
                        <div class="stat-card-lbl">H</div>
                    </div>
                    <div class="stat-card-box">
                        <div class="stat-card-num">
                            {{ isset($careerStats['AB']) && $careerStats['AB'] > 0 ? number_format($careerStats['H'] / $careerStats['AB'], 3) : '.000' }}
                        </div>
                        <div class="stat-card-lbl">AVG</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ⚠️ MANDATORY PROVENANCE DISCLOSURE [75, 115] -->
    @if($hasImportedStats)
        <div class="disclosure-footer">
            <strong>⚠️ Platform Verification Disclosure:</strong> This player card displays historical statistics uploaded from external spreadsheet files. Bulldog Statbook cannot independently verify the accuracy of imported offline sessions.
        </div>
    @else
        <div class="disclosure-footer" style="background: var(--green-soft); color: var(--green); border-color: var(--green);">
            <strong>✓ Verified Live Session Card:</strong> These statistics represent live, event-scored games finalized directly on the Bulldog Statbook scoring engine.
        </div>
    @endif

    <div class="branding-watermark">
        Generated by <strong>Bulldog Statbook</strong>
    </div>

</body>
</html>
