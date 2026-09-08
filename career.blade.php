<!-- resources/views/profile/career.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $player->display_name }} | Career History</title>
    <style>
        :root {
            --bg: #f3f5f4;
            --surface: #ffffff;
            --surface-alt: #f7f8f8;
            --text: #0d1b26;
            --text-muted: #5f6d78;
            --border: #d7dede;
            --border-strong: #b9c4c4;
            --navy: #081722;
            --navy-2: #102533;
            --green: #007a43;
            --green-hover: #00663a;
            --green-soft: #e4f3eb;
            --blue-accent: #0f6f97;
            --blue-soft: #e7f2f7;
            --warning-soft: #fff2d9;
            --warning-text: #9a6700;
            --danger-soft: #fde8e8;
            --danger-text: #b42318;
            --shadow-sm: 0 1px 2px rgba(8, 23, 34, 0.06);
            --shadow-md: 0 8px 24px rgba(8, 23, 34, 0.08);
            --radius-sm: 10px;
            --radius-md: 16px;
            --radius-lg: 20px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            padding: 2rem;
            line-height: 1.5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Whalers high-density header banner */
        .header-banner {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
            color: white;
            padding: 2.5rem;
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header-banner::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 100%;
            background: radial-gradient(circle, rgba(0,122,67,0.15) 0%, rgba(0,0,0,0) 70%);
            pointer-events: none;
        }

        .player-badge {
            display: inline-block;
            background-color: var(--green);
            color: white;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            letter-spacing: 1px;
            margin-bottom: 0.75rem;
        }

        .player-name {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            line-height: 1.1;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }

        .player-meta {
            display: flex;
            gap: 1.5rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.75);
            align-items: center;
        }

        .player-code {
            font-family: monospace;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 0.2rem 0.5rem;
            border-radius: var(--radius-sm);
            color: white;
            font-weight: bold;
        }

        /* Mandatory Limitation Disclaimer Banner [75, 115] */
        .disclaimer-banner {
            background-color: var(--warning-soft);
            border: 1px solid #f5e0b3;
            border-left: 5px solid var(--warning-text);
            padding: 1rem 1.5rem;
            border-radius: var(--radius-sm);
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            color: var(--warning-text);
            font-size: 0.925rem;
            box-shadow: var(--shadow-sm);
        }

        .disclaimer-icon {
            font-size: 1.5rem;
            font-weight: bold;
        }

        /* Info grids and cards */
        .grid-layout {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 2rem;
        }

        @media (max-width: 900px) {
            .grid-layout {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: var(--surface);
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--navy);
            margin-bottom: 1.25rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Statistics block styles */
        .stats-block-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .stat-card {
            background-color: var(--surface-alt);
            padding: 1rem;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            text-align: center;
        }

        .stat-card-value {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--navy);
            line-height: 1.2;
        }

        .stat-card-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        /* High-density interactive tables */
        .table-container {
            width: 100%;
            overflow-x: auto;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            margin-top: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.9rem;
        }

        th {
            background-color: var(--navy);
            color: white;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            padding: 0.85rem 1rem;
            letter-spacing: 0.5px;
        }

        td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--border);
            background-color: var(--surface);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: var(--surface-alt);
        }

        /* Provenance and Integrity Badges [75, 114, 150] */
        .provenance-badge {
            display: inline-flex;
            align-items: center;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            gap: 0.25rem;
        }

        .badge-derived {
            background-color: var(--green-soft);
            color: var(--green);
            border: 1px solid rgba(0, 122, 67, 0.2);
        }

        .badge-imported {
            background-color: var(--blue-soft);
            color: var(--blue-accent);
            border: 1px solid rgba(15, 111, 151, 0.2);
        }

        .badge-mixed {
            background-color: #fffaf0;
            color: #d97706;
            border: 1px solid rgba(217, 119, 6, 0.2);
        }

        /* Tab styles */
        .tabs-header {
            display: flex;
            gap: 0.5rem;
            border-bottom: 2px solid var(--border);
            margin-bottom: 1.5rem;
        }

        .tab-btn {
            padding: 0.75rem 1.25rem;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s ease;
        }

        .tab-btn.active {
            color: var(--green);
            border-bottom-color: var(--green);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>

<div class="container">

    <!-- ⚠️ Disclaimer Banner [75, 115] -->
    <div class="disclaimer-banner">
        <span class="disclaimer-icon">ℹ️</span>
        <div>
            <strong>Fidelity Disclosure Statement:</strong> Bulldog values longitudinal sports history but cannot independently verify uploaded/non-official roster entries, historic scorecards, or manually imported records.
        </div>
    </div>

    <!-- Athlete Career Header Workspace -->
    <div class="header-banner">
        <span class="player-badge">Claimed Athlete</span>
        <h1 class="player-name">{{ $player->display_name }}</h1>
        <div class="player-meta">
            <span>Official Identity Code: <span class="player-code">{{ $player->player_code }}</span></span>
            <span>•</span>
            <span>Primary Sport: <strong>{{ $player->primary_sport ?? 'Baseball' }}</strong></span>
            <span>•</span>
            <span>Verification Status: <strong style="color: #34d399;">✓ Claimed & Active</strong></span>
        </div>
    </div>

    <div class="grid-layout">
        
        <!-- Left Column: Career Totals Overview Card -->
        <div>
            <div class="card">
                <div class="card-title">Career Totals</div>
                
                <div class="stats-block-grid">
                    <div class="stat-card">
                        <div class="stat-card-value">{{ isset($careerStats['AB']) ? (int)$careerStats['AB']->stat_value : 0 }}</div>
                        <div class="stat-card-label">At Bats</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-value">{{ isset($careerStats['H']) ? (int)$careerStats['H']->stat_value : 0 }}</div>
                        <div class="stat-card-label">Hits</div>
                    </div>
                    <div class="stat-card" style="grid-column: span 2;">
                        @php
                            $ab = isset($careerStats['AB']) ? (float)$careerStats['AB']->stat_value : 0.0;
                            $h = isset($careerStats['H']) ? (float)$careerStats['H']->stat_value : 0.0;
                            $avg = $ab > 0 ? $h / $ab : 0.0;
                        @endphp
                        <div class="stat-card-value">{{ number_format($avg, 3) }}</div>
                        <div class="stat-card-label">Career AVG</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-value">{{ isset($careerStats['HR']) ? (int)$careerStats['HR']->stat_value : 0 }}</div>
                        <div class="stat-card-label">Home Runs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-value">{{ isset($careerStats['RBI']) ? (int)$careerStats['RBI']->stat_value : 0 }}</div>
                        <div class="stat-card-label">RBI</div>
                    </div>
                    <div class="stat-card" style="grid-column: span 2;">
                        <div class="stat-card-value">{{ isset($careerStats['BB']) ? (int)$careerStats['BB']->stat_value : 0 }}</div>
                        <div class="stat-card-label">Walks</div>
                    </div>
                </div>
            </div>

            <!-- Provenance Ledger Card [75, 142] -->
            <div class="card">
                <div class="card-title">Provenance Ledger</div>
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                    Provenance details for tracked statistics and unverified files linked to this athlete identity profile.
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
                        <span style="font-size: 0.8rem; font-weight: 700;">Live Event Scoring</span>
                        <span class="provenance-badge badge-derived">Bulldog Derived</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
                        <span style="font-size: 0.8rem; font-weight: 700;">External CSV Imports</span>
                        <span class="provenance-badge badge-imported">Imported File</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 0.5rem;">
                        <span style="font-size: 0.8rem; font-weight: 700;">Combined Aggregates</span>
                        <span class="provenance-badge badge-mixed">Mixed Records</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Interactive Season breakdown -->
        <div>
            <div class="card">
                <div class="tabs-header">
                    <button class="tab-btn active" onclick="switchTab(event, 'all-seasons')">Season Overview</button>
                    <button class="tab-btn" onclick="switchTab(event, 'source-provenance')">Import Audits</button>
                </div>

                <!-- Tab 1: Combined Season Aggregates -->
                <div id="all-seasons" class="tab-content active">
                    <div class="card-title" style="border: none; margin-bottom: 0;">Season History Grid</div>
                    
                    @if(count($seasonStats) === 0)
                        <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            No statistical records exist yet for this athlete identity context.
                        </div>
                    @else
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Season</th>
                                        <th>AB</th>
                                        <th>H</th>
                                        <th>HR</th>
                                        <th>RBI</th>
                                        <th>BB</th>
                                        <th>AVG</th>
                                        <th>Provenance Context</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($seasonStats as $label => $stats)
                                        @php
                                            $abRow = $stats->firstWhere('stat_key', 'AB')?->stat_value ?? 0;
                                            $hRow = $stats->firstWhere('stat_key', 'H')?->stat_value ?? 0;
                                            $hrRow = $stats->firstWhere('stat_key', 'HR')?->stat_value ?? 0;
                                            $rbiRow = $stats->firstWhere('stat_key', 'RBI')?->stat_value ?? 0;
                                            $bbRow = $stats->firstWhere('stat_key', 'BB')?->stat_value ?? 0;
                                            $avgRow = $abRow > 0 ? $hRow / $abRow : 0.0;
                                            
                                            // Determine overall provenance flags for the row
                                            $sourceType = $stats->firstWhere('stat_key', 'AB')?->source_type ?? 'bulldog_derived';
                                            $fidelity = $stats->firstWhere('stat_key', 'AB')?->fidelity_level ?? 'event_complete';
                                        @endphp
                                        <tr>
                                            <td><strong>{{ $label }}</strong></td>
                                            <td>{{ (int)$abRow }}</td>
                                            <td>{{ (int)$hRow }}</td>
                                            <td>{{ (int)$hrRow }}</td>
                                            <td>{{ (int)$rbiRow }}</td>
                                            <td>{{ (int)$bbRow }}</td>
                                            <td><strong>{{ number_format($avgRow, 3) }}</strong></td>
                                            <td>
                                                @if($sourceType === 'bulldog_derived')
                                                    <span class="provenance-badge badge-derived">Bulldog Active</span>
                                                @elseif($sourceType === 'mixed')
                                                    <span class="provenance-badge badge-mixed">Mixed Sources</span>
                                                @else
                                                    <span class="provenance-badge badge-imported" title="Fidelity: {{ $fidelity }}">Imported ({{ $fidelity }})</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Tab 2: Upload History Audits -->
                <div id="source-provenance" class="tab-content">
                    <div class="card-title" style="border: none; margin-bottom: 0;">Upload Source Provenance</div>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                        This list logs external CSV files and manual scorecards parsed and confirmed for this player profile [83, 114].
                    </p>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Import Identifier</th>
                                    <th>Source Label</th>
                                    <th>Fidelity Level</th>
                                    <th>Verification Status</th>
                                    <th>Uploader</th>
                                    <th>Notes</th>
                                    <th>Date Linked</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // Normally fetched via relationship; hardcoded fallback here for layout illustration
                                    $sampleImports = [
                                        [
                                            'code' => 'IMP-472FA9',
                                            'label' => 'GameChanger Summer 2025 Export',
                                            'fidelity' => 'season_totals',
                                            'status' => 'approved',
                                            'uploader' => 'Coach Hayes',
                                            'notes' => 'Imported player stats from 2025 Summer league.',
                                            'date' => '2026-09-08'
                                        ]
                                    ];
                                @endphp
                                @foreach($sampleImports as $imp)
                                    <tr>
                                        <td><span class="player-code">{{ $imp['code'] }}</span></td>
                                        <td><strong>{{ $imp['label'] }}</strong></td>
                                        <td><span class="provenance-badge badge-imported">{{ $imp['fidelity'] }}</span></td>
                                        <td><strong style="color: var(--green);">✓ Approved</strong></td>
                                        <td>{{ $imp['uploader'] }}</td>
                                        <td><small>{{ $imp['notes'] }}</small></td>
                                        <td>{{ $imp['date'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>

<script>
    function switchTab(evt, tabId) {
        // Hide all contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });

        // Remove active class from buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // Show active tab and add active class to clicked button
        document.getElementById(tabId).classList.add('active');
        evt.currentTarget.classList.add('active');
    }
</script>

</body>
</html>
