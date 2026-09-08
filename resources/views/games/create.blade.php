<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Matchup | Bulldog Statbook</title>
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
            --radius-md: 12px;
            --radius-sm: 8px;
            --shadow-md: 0 4px 20px rgba(8, 23, 34, 0.08);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
        }

        /* Responsive Layout Grid */
        .app-container {
            display: grid;
            grid-template-columns: 260px 1fr;
            width: 100%;
        }

        /* Sidebar Navigation styling matching Dashboard */
        .sidebar {
            background-color: var(--navy);
            color: white;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 32px;
        }

        .brand {
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            color: var(--surface);
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 12px;
        }

        .brand span {
            color: var(--green);
        }

        .nav-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .nav-links a {
            color: #b0c0cf;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            transition: all 0.2s ease;
        }

        .nav-links a:hover, .nav-links .active {
            color: white;
            background-color: rgba(255, 255, 255, 0.08);
        }

        .nav-links .active {
            border-left: 4px solid var(--green);
        }

        /* Main Workspace Content Area */
        .main-content {
            padding: 40px;
            overflow-y: auto;
        }

        .header-section {
            margin-bottom: 32px;
        }

        .header-section h1 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--navy);
            margin: 0 0 8px 0;
        }

        .header-section p {
            color: var(--text-muted);
            margin: 0;
            font-size: 1.05rem;
        }

        /* Form Card Styling */
        .card {
            background-color: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            padding: 32px;
            max-width: 800px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        label {
            font-weight: 700;
            color: var(--navy);
            font-size: 0.95rem;
        }

        select, input {
            padding: 12px;
            border: 1px solid var(--border-strong);
            border-radius: var(--radius-sm);
            font-size: 1rem;
            color: var(--text);
            background-color: var(--surface-alt);
            transition: border-color 0.2s ease;
        }

        select:focus, input:focus {
            outline: none;
            border-color: var(--blue-accent);
            background-color: var(--surface);
        }

        /* Alerts and errors */
        .alert {
            padding: 16px;
            border-radius: var(--radius-sm);
            margin-bottom: 24px;
            font-weight: 600;
        }

        .alert-danger {
            background-color: var(--danger-soft);
            color: var(--danger-text);
            border: 1px solid rgba(180, 35, 24, 0.2);
        }

        .error-msg {
            color: var(--danger-text);
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 4px;
        }

        /* Action Buttons */
        .action-bar {
            display: flex;
            justify-content: flex-end;
            gap: 16px;
            border-top: 1px solid var(--border);
            padding-top: 24px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: var(--radius-sm);
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .btn-secondary {
            background-color: var(--surface-alt);
            border: 1px solid var(--border-strong);
            color: var(--text-muted);
        }

        .btn-secondary:hover {
            background-color: var(--border);
            color: var(--text);
        }

        .btn-primary {
            background-color: var(--green);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background-color: var(--green-hover);
        }

        @media (max-width: 900px) {
            .app-container {
                grid-template-columns: 1fr;
            }
            .sidebar {
                display: none;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .form-group.full-width {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Branded Left Sidebar -->
        <aside class="sidebar">
            <div class="brand">BULLDOG <span>STATBOOK</span></div>
            <nav>
                <ul class="nav-links">
                    <li><a href="/dashboard">Dashboard</a></li>
                    <li><a href="/teams" class="active">My Teams</a></li>
                    <li><a href="/players">Player Identity</a></li>
                    <li><a href="/guardians">Guardians</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Workspace -->
        <main class="main-content">
            <div class="header-section">
                <h1>Schedule Matchup</h1>
                <p>Set up a new baseball or softball game and load your rosters instantly into live play [108].</p>
            </div>

            <div class="card">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        @if ($errors->has('error'))
                            {{ $errors->first('error') }}
                        @else
                            Please resolve the validation errors highlighted below.
                        @endif
                    </div>
                @endif

                <form action="/games" method="POST" id="scheduleForm">
                    @csrf

                    <div class="form-grid">
                        <!-- Home Team Selection -->
                        <div class="form-group">
                            <label id="homeTeamLabel" for="home_team_id">Home Team (Your Managed Teams)</label>
                            <select name="home_team_id" id="home_team_id" required onchange="filterOpponentsAndRules()">
                                <option value="" disabled selected>-- Select Home Team --</option>
                                @foreach ($myTeams as $team)
                                    <option value="{{ $team->id }}" data-sport-id="{{ $team->sport_id }}" {{ old('home_team_id') == $team->id ? 'selected' : '' }}>
                                        {{ $team->name }} ({{ $team->sport->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('home_team_id')
                                <span class="error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Away Team Selection (Opponents) -->
                        <div class="form-group">
                            <label id="awayTeamLabel" for="away_team_id">Away Team (Opponent)</label>
                            <select name="away_team_id" id="away_team_id" required disabled>
                                <option value="" disabled selected>-- Select Opponent --</option>
                                @foreach ($opponents as $opponent)
                                    <option value="{{ $opponent->id }}" data-sport-id="{{ $opponent->sport_id }}" {{ old('away_team_id') == $opponent->id ? 'selected' : '' }}>
                                        {{ $opponent->name }} ({{ $opponent->sport->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('away_team_id')
                                <span class="error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Ruleset Selection -->
                        <div class="form-group full-width">
                            <label id="rulesetLabel" for="ruleset_id">Ruleset & Gameplay Configuration [137, 260]</label>
                            <select name="ruleset_id" id="ruleset_id" required disabled>
                                <option value="" disabled selected>-- Select Ruleset --</option>
                                @foreach ($rulesets as $ruleset)
                                    <option value="{{ $ruleset->id }}" data-sport-id="{{ $ruleset->sport_id }}" {{ old('ruleset_id') == $ruleset->id ? 'selected' : '' }}>
                                        {{ $ruleset->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ruleset_id')
                                <span class="error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Scheduled Date/Time -->
                        <div class="form-group">
                            <label id="dateLabel" for="scheduled_at">Scheduled At</label>
                            <input type="datetime-local" name="scheduled_at" id="scheduled_at" required value="{{ old('scheduled_at') }}">
                            @error('scheduled_at')
                                <span class="error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Location -->
                        <div class="form-group">
                            <label id="locationLabel" for="location">Location / Field</label>
                            <input type="text" name="location" id="location" placeholder="e.g., Diamond Field #4" value="{{ old('location') }}">
                            @error('location')
                                <span class="error-msg">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="action-bar">
                        <a href="/dashboard" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Schedule & Initialize Game</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- Client-Side Filter Logic -->
    <script>
        function filterOpponentsAndRules() {
            const homeSelect = document.getElementById('home_team_id');
            const awaySelect = document.getElementById('away_team_id');
            const rulesetSelect = document.getElementById('ruleset_id');

            const selectedOption = homeSelect.options[homeSelect.selectedIndex];
            if (!selectedOption || selectedOption.value === "") {
                awaySelect.disabled = true;
                rulesetSelect.disabled = true;
                return;
            }

            const activeSportId = selectedOption.getAttribute('data-sport-id');

            // 1. Filter Opponent Candidates
            awaySelect.disabled = false;
            awaySelect.selectedIndex = 0; // Reset opponent dropdown selection

            for (let i = 0; i < awaySelect.options.length; i++) {
                const opt = awaySelect.options[i];
                if (opt.value === "") continue;

                const opponentSportId = opt.getAttribute('data-sport-id');
                const opponentTeamId = opt.value;

                // Match only same sport AND prevent playing oneself
                if (opponentSportId === activeSportId && opponentTeamId !== selectedOption.value) {
                    opt.style.display = 'block';
                    opt.disabled = false;
                } else {
                    opt.style.display = 'none';
                    opt.disabled = true;
                }
            }

            // 2. Filter Rulesets
            rulesetSelect.disabled = false;
            rulesetSelect.selectedIndex = 0; // Reset ruleset selection

            for (let i = 0; i < rulesetSelect.options.length; i++) {
                const opt = rulesetSelect.options[i];
                if (opt.value === "") continue;

                const rulesetSportId = opt.getAttribute('data-sport-id');

                if (rulesetSportId === activeSportId) {
                    opt.style.display = 'block';
                    opt.disabled = false;
                } else {
                    opt.style.display = 'none';
                    opt.disabled = true;
                }
            }
        }

        // Initialize filtering on page load if old inputs exist
        window.onload = function() {
            if (document.getElementById('home_team_id').value !== "") {
                filterOpponentsAndRules();
            }
        };
    </script>
</body>
</html>