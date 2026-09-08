<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duplicate Player Resolver | Bulldog Admin</title>
    <!-- Sharp, high-density styling variables aligned to official brand reference [21, 331] -->
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

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }

        /* Desktop Layout [2, 323] */
        .admin-container {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }

        /* Sidebar Navigation */
        .sidebar {
            background-color: var(--navy);
            color: white;
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--navy-2);
        }

        .brand-logo {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.05em;
            margin-bottom: 2.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-transform: uppercase;
        }

        .brand-logo span {
            color: var(--green);
        }

        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .nav-item a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm);
            display: block;
            font-weight: 600;
            transition: all 0.2s;
        }

        .nav-item.active a, .nav-item a:hover {
            color: white;
            background-color: var(--navy-2);
        }

        /* Main Workspace Content [2, 326] */
        .main-workspace {
            padding: 2.5rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            box-sizing: border-box;
        }

        .workspace-header {
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 1.5rem;
        }

        .workspace-header h1 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--navy);
            margin: 0 0 0.5rem 0;
            letter-spacing: -0.03em;
        }

        .workspace-header p {
            color: var(--text-muted);
            margin: 0;
            font-size: 1.1rem;
        }

        /* Alert and Notifications */
        .alert {
            padding: 1.25rem;
            border-radius: var(--radius-md);
            margin-bottom: 2rem;
            font-weight: 600;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .alert-success {
            background-color: var(--green-soft);
            color: var(--green-hover);
            border: 1px solid rgba(0, 122, 67, 0.2);
        }

        .alert-warning {
            background-color: var(--warning-soft);
            color: var(--warning-text);
            border: 1px solid rgba(154, 103, 0, 0.2);
        }

        /* Resolution Grid [1, 21] */
        .resolution-card {
            background-color: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 2.5rem;
            overflow: hidden;
        }

        .resolution-card-header {
            background-color: var(--surface-alt);
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .resolution-card-header h3 {
            margin: 0;
            font-size: 1.25rem;
            color: var(--navy);
            font-weight: 800;
        }

        .sport-badge {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            background-color: var(--blue-soft);
            color: var(--blue-accent);
            border: 1px solid rgba(15, 111, 151, 0.2);
        }

        .profiles-comparison {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            padding: 2rem;
            background-color: var(--surface);
        }

        .profile-column {
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            transition: all 0.2s;
            position: relative;
        }

        .profile-column.selected-canonical {
            border-color: var(--green);
            background-color: var(--green-soft);
            box-shadow: 0 0 0 4px rgba(0, 122, 67, 0.15);
        }

        .profile-column.selected-duplicate {
            border-color: var(--danger-text);
            background-color: var(--danger-soft);
            box-shadow: 0 0 0 4px rgba(180, 35, 24, 0.15);
        }

        .profile-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .profile-heading h4 {
            margin: 0;
            font-size: 1.15rem;
            color: var(--navy);
            font-weight: 700;
        }

        .status-badge {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 0.25rem 0.6rem;
            border-radius: 4px;
        }

        .status-claimed {
            background-color: var(--green-soft);
            color: var(--green);
        }

        .status-unclaimed {
            background-color: #f0f0f0;
            color: var(--text-muted);
            border: 1px solid var(--border);
        }

        .stat-meta-list {
            margin: 0 0 1.5rem 0;
            padding: 0;
            list-style: none;
            font-size: 0.95rem;
        }

        .stat-meta-list li {
            padding: 0.5rem 0;
            border-bottom: 1px dashed var(--border);
            display: flex;
            justify-content: space-between;
        }

        .stat-meta-list li:last-child {
            border-bottom: none;
        }

        .stat-meta-list span {
            font-weight: 600;
        }

        /* Action Forms */
        .merge-form-bar {
            background-color: var(--surface-alt);
            padding: 1.5rem 2rem;
            border-top: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .form-row {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .form-group {
            flex: 1;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            font-size: 1rem;
            background-color: white;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--blue-accent);
        }

        /* Visual Warning Panel */
        .safety-disclaimer {
            background-color: var(--warning-soft);
            border-left: 4px solid var(--warning-text);
            padding: 1rem 1.25rem;
            border-radius: 0 var(--radius-md) var(--radius-md) 0;
            font-size: 0.9rem;
            color: #7b5200;
        }

        .safety-disclaimer ul {
            margin: 0.5rem 0 0 0;
            padding-left: 1.25rem;
        }

        .merge-btn-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 0.5rem;
        }

        .btn {
            padding: 0.85rem 1.75rem;
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background-color: var(--green);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--green-hover);
        }

        .btn-primary:disabled {
            background-color: var(--border-strong);
            cursor: not-allowed;
        }

        .selection-radio {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            transform: scale(1.5);
            cursor: pointer;
        }
    </style>
</head>
<body>

    <div class="admin-container">
        <!-- APP SIDEBAR SHELL [9] -->
        <aside class="sidebar">
            <div class="brand-logo">
                Bulldog <span>Admin</span>
            </div>
            <nav>
                <ul class="nav-list">
                    <li class="nav-item"><a href="#">Dashboard Home</a></li>
                    <li class="nav-item active"><a href="#">Identity Resolution</a></li>
                    <li class="nav-item"><a href="#">Historical Imports</a></li>
                    <li class="nav-item"><a href="#">Audit Event Log</a></li>
                </ul>
            </nav>
        </aside>

        <!-- MAIN WORKSPACE -->
        <main class="main-workspace">
            <header class="workspace-header">
                <h1>Duplicate Profile Resolution</h1>
                <p>Detect and resolve split player accounts to preserve accurate season and career statistical histories.</p>
            </header>

            <!-- STATUS MESSAGES -->
            @if (session('status'))
                <div class="alert alert-success">
                    <strong>✓ Success:</strong> {{ session('status') }}
                </div>
            @endif

            @if ($errors->has('merge_error'))
                <div class="alert alert-warning" style="background-color: var(--danger-soft); color: var(--danger-text); border-color: rgba(180,35,24,0.2);">
                    <strong>⚠️ Error:</strong> {{ $errors->first('merge_error') }}
                </div>
            @endif

            <!-- INFORMATION SUMMARY -->
            <div class="alert alert-warning">
                <strong>💡 Re-calculation Guarantee:</strong> Merging records transfers all roster spots, defensive lineups, and raw event records. Career totals are automatically re-calculated from the event stream, preventing data drift [59, 148].
            </div>

            <!-- UNRESOLVED DETECTED DUPLICATES -->
            @forelse ($duplicates as $group)
                <div class="resolution-card">
                    <div class="resolution-card-header">
                        <h3>Potential Duplicate: "{{ $group['name'] }}"</h3>
                        <span class="sport-badge">{{ $group['sport'] }}</span>
                    </div>

                    <!-- PROFILE DETAIL TILES -->
                    <div class="profiles-comparison">
                        @foreach ($group['profiles'] as $index => $profile)
                            <div class="profile-column" id="col-{{ $profile->id }}">
                                <div class="profile-heading">
                                    <h4>Profile Option #{{ $index + 1 }}</h4>
                                    <span class="status-badge {{ $profile->claim_status === 'claimed' ? 'status-claimed' : 'status-unclaimed' }}">
                                        {{ $profile->claim_status }}
                                    </span>
                                </div>

                                <ul class="stat-meta-list">
                                    <li>Player Code: <span>{{ $profile->player_code }}</span></li>
                                    <li>Linked User Account: <span>{{ $profile->user ? $profile->user->email : 'None (Unclaimed Roster Profile)' }}</span></li>
                                    <li>Rostered Teams: 
                                        <span>
                                            @foreach ($profile->teamMemberships as $membership)
                                                {{ $membership->team->name }} ({{ $membership->jersey_number }})@if(!$loop->last), @endif
                                            @endforeach
                                        </span>
                                    </li>
                                    <li>Created: <span>{{ $profile->created_at->format('M d, Y') }}</span></li>
                                </ul>

                                <!-- RADION BUTTON TO ESTABLISH DIRECTION -->
                                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-weight: 700; font-size: 0.95rem;">
                                    <input type="radio" 
                                           name="selection_for_{{ md5($group['name']) }}" 
                                           class="profile-selector" 
                                           data-group="{{ md5($group['name']) }}" 
                                           data-id="{{ $profile->id }}"
                                           onchange="handleSelection('{{ md5($group['name']) }}', '{{ $profile->id }}')">
                                    Set as Canonical (Surviving) Profile
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <!-- FORM TRIGGER -->
                    <form action="{{ route('admin.players.merge') }}" method="POST" class="merge-form-bar" id="form-{{ md5($group['name']) }}">
                        @csrf
                        <input type="hidden" name="canonical_identity_id" id="canonical-{{ md5($group['name']) }}">
                        <input type="hidden" name="duplicate_identity_id" id="duplicate-{{ md5($group['name']) }}">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="reason-{{ md5($group['name']) }}">Administrative Reason for Merge</label>
                                <input type="text" 
                                       name="reason" 
                                       id="reason-{{ md5($group['name']) }}" 
                                       class="form-input" 
                                       placeholder="e.g., Coach created unclaimed roster entry, merging with official registered user account." 
                                       required>
                            </div>
                        </div>

                        <!-- SECURITY WARNING & GUARANTEES -->
                        <div class="safety-disclaimer">
                            <strong>⚠️ Safety Actions Following Execution:</strong>
                            <ul>
                                <li>The non-surviving duplicate player record (and its code) will be permanently deleted from database lookups [80].</li>
                                <li>All historic team memberships, live scoring events, and uploaded stat sheets will be re-assigned.</li>
                                <li><strong>The aggregate ledger will dynamically recompute all metrics from raw logs to secure 100% data fidelity [71].</strong></li>
                            </ul>
                        </div>

                        <div class="merge-btn-container">
                            <button type="submit" 
                                    class="btn btn-primary" 
                                    id="btn-{{ md5($group['name']) }}" 
                                    disabled>
                                Resolve & Merge Profiles
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="resolution-card" style="padding: 3rem; text-align: center; color: var(--text-muted);">
                    <h3 style="margin-bottom: 0.5rem;">No Duplicate Profiles Detected</h3>
                    <p style="margin: 0;">Your player index is healthy! Duplicate profiles will automatically list here when matching names are discovered.</p>
                </div>
            @endforelse
        </main>
    </div>

    <!-- CLIENT-SIDE VISUAL DIRECTION CONTROLLER [48] -->
    <script>
        function handleSelection(groupHash, selectedCanonicalId) {
            const container = document.getElementById('form-' + groupHash);
            const canonicalInput = document.getElementById('canonical-' + groupHash);
            const duplicateInput = document.getElementById('duplicate-' + groupHash);
            const submitButton = document.getElementById('btn-' + groupHash);

            // Fetch the profiles in this group
            const groupSelectors = document.querySelectorAll(`input[data-group="${groupHash}"]`);
            let duplicateId = null;

            groupSelectors.forEach(selector => {
                const profileId = selector.getAttribute('data-id');
                const column = document.getElementById('col-' + profileId);

                if (profileId === selectedCanonicalId) {
                    column.className = 'profile-column selected-canonical';
                } else {
                    column.className = 'profile-column selected-duplicate';
                    duplicateId = profileId; // Set the non-selected profile as the duplicate to absorb
                }
            });

            // Store inside hidden forms
            canonicalInput.value = selectedCanonicalId;
            duplicateInput.value = duplicateId;

            // Enable confirmation button
            submitButton.disabled = false;
        }
    </script>
</body>
</html>
