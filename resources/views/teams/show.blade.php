@extends('layouts.app')

@section('content')
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
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
        --shadow-sm: 0 1px 2px rgba(8, 23, 34, 0.06);
        --shadow-md: 0 4px 12px rgba(8, 23, 34, 0.08);
    }

    body {
        background-color: var(--bg);
        color: var(--text);
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    /* Team Hero Banner */
    .team-hero {
        background: linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
        color: #ffffff;
        padding: 2.5rem 2rem;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .team-hero::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 300px;
        height: 100%;
        background: linear-gradient(90deg, transparent 0%, rgba(0, 122, 67, 0.1) 100%);
        pointer-events: none;
    }

    .team-badge-icon {
        background-color: var(--green);
        color: #ffffff;
        font-size: 1.5rem;
        font-weight: 800;
        text-transform: uppercase;
        width: 64px;
        height: 64px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid rgba(255, 255, 255, 0.2);
    }

    .team-code-pill {
        background-color: rgba(255, 255, 255, 0.15);
        color: #ffffff;
        font-family: monospace;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.85rem;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }

    /* Custom App Tabs */
    .nav-tabs-custom {
        display: flex;
        gap: 1rem;
        border-bottom: 2px solid var(--border);
        margin-bottom: 2rem;
        padding-bottom: 0.25rem;
    }

    .tab-btn {
        background: none;
        border: none;
        color: var(--text-muted);
        font-weight: 700;
        font-size: 1rem;
        padding: 0.75rem 1.25rem;
        cursor: pointer;
        transition: all 0.2s ease;
        border-radius: var(--radius-sm) var(--radius-sm) 0 0;
        position: relative;
    }

    .tab-btn:hover {
        color: var(--navy);
    }

    .tab-btn.active {
        color: var(--navy);
    }

    .tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -6px;
        left: 0;
        width: 100%;
        height: 4px;
        background-color: var(--green);
        border-radius: 2px;
    }

    .tab-content-pane {
        display: none;
    }

    .tab-content-pane.active {
        display: block;
    }

    /* Branded Badges */
    .badge-custom {
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .badge-claimed { background-color: var(--green-soft); color: var(--green); }
    .badge-unclaimed { background-color: var(--warning-soft); color: var(--warning-text); }
    .badge-pending { background-color: var(--blue-soft); color: var(--blue-accent); }
    .badge-disputed { background-color: var(--danger-soft); color: var(--danger-text); }

    .badge-role-admin { background-color: var(--navy-2); color: #ffffff; }
    .badge-role-coach { background-color: var(--blue-accent); color: #ffffff; }
    .badge-role-scorekeeper { background-color: #f79009; color: #ffffff; }

    /* Tables & Cards */
    .card-custom {
        background-color: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .table-custom {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-custom th {
        background-color: var(--surface-alt);
        color: var(--navy);
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1rem;
        text-align: left;
        border-bottom: 2px solid var(--border);
    }

    .table-custom td {
        padding: 1rem;
        border-bottom: 1px solid var(--border);
        font-size: 0.95rem;
        color: var(--text);
        vertical-align: middle;
    }

    .table-custom tr:hover td {
        background-color: var(--surface-alt);
    }

    .table-custom tr:last-child td {
        border-bottom: none;
    }

    /* Grid Layouts */
    .metric-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .metric-card-small {
        background-color: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: var(--shadow-sm);
    }

    .metric-card-small .label {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 0.5rem;
    }

    .metric-card-small .value {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--navy);
    }

    /* Branded Forms & Inputs */
    .input-custom {
        width: 100%;
        border: 1px solid var(--border-strong);
        border-radius: var(--radius-sm);
        padding: 0.65rem 0.85rem;
        font-size: 0.95rem;
        color: var(--text);
        background-color: var(--surface);
        transition: border-color 0.15s ease;
    }

    .input-custom:focus {
        border-color: var(--blue-accent);
        outline: none;
        box-shadow: 0 0 0 3px var(--blue-soft);
    }

    .btn-brand-primary {
        background-color: var(--green);
        color: #ffffff;
        font-weight: 700;
        border: none;
        border-radius: var(--radius-sm);
        padding: 0.65rem 1.25rem;
        cursor: pointer;
        transition: background-color 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-brand-primary:hover {
        background-color: var(--green-hover);
    }

    .btn-brand-secondary {
        background-color: var(--surface);
        color: var(--navy);
        border: 1px solid var(--border-strong);
        border-radius: var(--radius-sm);
        padding: 0.65rem 1.25rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .btn-brand-secondary:hover {
        background-color: var(--surface-alt);
        border-color: var(--navy);
    }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Team Hero Context Header -->
    <div class="team-hero">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="team-badge-icon">
                    {{ substr($team->name, 0, 2) }}
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-extrabold tracking-tight">{{ $team->name }}</h1>
                        <span class="team-code-pill">{{ $team->team_code }}</span>
                    </div>
                    <p class="text-sm text-gray-300 mt-1">
                        {{ $team->sport->name }} &middot; {{ $team->age_group ?? 'All Ages' }} &middot; {{ $team->season_label ?? 'Unscheduled Season' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @can('update', $team)
                    <a href="{{ url('/teams/' . $team->id . '/edit') }}" class="btn-brand-secondary bg-transparent text-white border-white hover:bg-white hover:text-navy">
                        Edit Settings
                    </a>
                @endcan
                <a href="{{ url('/dashboard') }}" class="btn-brand-secondary bg-transparent text-white border-white hover:bg-white hover:text-navy">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Stats summary context block -->
    <div class="metric-grid">
        <div class="metric-card-small">
            <span class="label">Roster Size</span>
            <span class="value">{{ $team->memberships->where('membership_type', 'player')->count() }}</span>
        </div>
        <div class="metric-card-small">
            <span class="label">Staff & Staff-roles</span>
            <span class="value">{{ $team->roleAssignments->count() }}</span>
        </div>
        <div class="metric-card-small">
            <span class="label">Team Status</span>
            <span class="value" style="color: var(--green)">{{ ucfirst($team->status) }}</span>
        </div>
    </div>

    <!-- Custom Navigation Tabs -->
    <div class="nav-tabs-custom">
        <button class="tab-btn active" onclick="switchTab(event, 'overview-pane')">Overview</button>
        <button class="tab-btn" onclick="switchTab(event, 'roster-pane')">Active Roster</button>
        <button class="tab-btn" onclick="switchTab(event, 'roles-pane')">Roles Dashboard</button>
    </div>

    <!-- Alert Messaging -->
    @if(session('success'))
        <div class="p-4 mb-6 text-green-800 bg-green-100 rounded-lg border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 mb-6 text-red-800 bg-red-100 rounded-lg border border-red-200">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- TAB 1: OVERVIEW -->
    <div id="overview-pane" class="tab-content-pane active">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="card-custom">
                    <h3 class="text-lg font-bold mb-4">Operations Summary</h3>
                    <p class="text-sm text-gray-600 mb-6">
                        Welcome to the <strong>{{ $team->name }}</strong> operational command center. From here you can handle roster assignments, player identity claim reviews, and contextual coaching permissions.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                            <h4 class="font-bold text-sm mb-1 text-navy">Primary Sport</h4>
                            <p class="text-lg font-extrabold text-green-700">{{ $team->sport->name }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                            <h4 class="font-bold text-sm mb-1 text-navy">Age Group Context</h4>
                            <p class="text-lg font-extrabold text-green-700">{{ $team->age_group ?? 'General / Not Set' }}</p>
                        </div>
                    </div>
                </div>

                <div class="card-custom">
                    <h3 class="text-lg font-bold mb-4">Recent Activity Logs</h3>
                    <div class="border-l-4 border-green-500 pl-4 py-1">
                        <p class="text-sm font-semibold">Roster Slot Created</p>
                        <p class="text-xs text-gray-500">A new unclaimed athlete roster position was added to the fall schedule.</p>
                    </div>
                </div>
            </div>

            <!-- Quick Action Drawer -->
            <div>
                <div class="card-custom">
                    <h3 class="text-md font-extrabold mb-4 uppercase tracking-wider text-navy">Quick Operational Actions</h3>
                    <div class="flex flex-col gap-3">
                        @can('manageRoster', $team)
                            <button onclick="switchTab(null, 'roster-pane')" class="btn-brand-primary justify-center text-center">
                                Manage Team Roster
                            </button>
                        @endcan
                        @can('assignRoles', $team)
                            <button onclick="switchTab(null, 'roles-pane')" class="btn-brand-secondary">
                                Manage Roles & Invite Staff
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: ACTIVE ROSTER -->
    <div id="roster-pane" class="tab-content-pane">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2">
                <div class="card-custom">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold">Roster Roster Structure</h3>
                        <span class="text-xs text-gray-500">Showing active player memberships</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Player / Athlete</th>
                                    <th>Athlete Code</th>
                                    <th>Jersey #</th>
                                    <th>Field Positions</th>
                                    <th>Claim Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($team->memberships->where('membership_type', 'player') as $membership)
                                    <tr>
                                        <td>
                                            <div class="font-bold">{{ $membership->playerIdentity->display_name }}</div>
                                            <div class="text-xs text-gray-400">Rostered since {{ $membership->created_at->format('M d, Y') }}</div>
                                        </td>
                                        <td>
                                            <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded text-navy-2">
                                                {{ $membership->playerIdentity->player_code }}
                                            </code>
                                        </td>
                                        <td class="font-semibold text-center">{{ $membership->jersey_number ?? '-' }}</td>
                                        <td>
                                            @if($membership->positions_json)
                                                <div class="flex gap-1 flex-wrap">
                                                    @foreach($membership->positions_json as $pos)
                                                        <span class="text-xs bg-gray-100 border border-gray-200 px-1.5 py-0.5 rounded font-mono font-bold">{{ $pos }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400">None Set</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge-custom badge-{{ $membership->playerIdentity->claim_status }}">
                                                {{ $membership->playerIdentity->claim_status }}
                                            </span>
                                        </td>
                                        <td>
                                            @can('manageRoster', $team)
                                                <div class="flex gap-2">
                                                    <a href="{{ url('/teams/' . $team->id . '/roster/' . $membership->id . '/edit') }}" class="text-sm font-bold text-blue-accent hover:underline">
                                                        Edit
                                                    </a>
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400">Locked</span>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-8 text-gray-400">
                                            No active player identities found on this roster. Use the panel on the right to build your roster!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Inline Roster Form Box -->
            @can('manageRoster', $team)
                <div>
                    <div class="card-custom">
                        <h3 class="text-md font-extrabold mb-4 uppercase tracking-wider text-navy">Add Player to Roster</h3>
                        
                        <form method="POST" action="{{ url('/teams/' . $team->id . '/roster') }}">
                            @csrf
                            
                            <!-- Roster Pathway Selector -->
                            <div class="mb-4">
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Roster Pathway</label>
                                <select id="pathway-select" onchange="toggleRosterPathway()" class="input-custom">
                                    <option value="unclaimed">Option B: Create New Unclaimed Athlete Profile</option>
                                    <option value="existing">Option A: Link Pre-Existing Athlete Code</option>
                                </select>
                            </div>

                            <!-- Form Field inputs: Existing Player -->
                            <div id="existing-player-fields" style="display: none;" class="mb-4">
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Existing Player Identity UUID</label>
                                <input type="text" name="player_identity_id" placeholder="Paste full athlete UUID" class="input-custom">
                            </div>

                            <!-- Form Field inputs: Brand New Athlete Profile -->
                            <div id="new-player-fields" class="mb-4">
                                <div class="mb-4">
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Athlete Display Name *</label>
                                    <input type="text" name="display_name" placeholder="First and last name" class="input-custom">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Birth Year</label>
                                    <input type="number" name="birth_year" placeholder="e.g. 2014" min="1900" max="{{ date('Y') }}" class="input-custom">
                                </div>
                            </div>

                            <!-- Shared Fields -->
                            <div class="mb-4">
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Jersey Number</label>
                                <input type="text" name="jersey_number" placeholder="e.g. 18" class="input-custom">
                            </div>

                            <div class="mb-6">
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Field Positions</label>
                                <div class="grid grid-cols-3 gap-2">
                                    @php
                                        $positions = $team->sport->code === 'softball' 
                                            ? ['P', 'C', '1B', '2B', '3B', 'SS', 'LF', 'CF', 'RF', 'DP'] 
                                            : ['P', 'C', '1B', '2B', '3B', 'SS', 'LF', 'CF', 'RF', 'DH'];
                                    @endphp
                                    @foreach($positions as $pos)
                                        <label class="flex items-center gap-1.5 text-xs font-semibold cursor-pointer">
                                            <input type="checkbox" name="positions[]" value="{{ $pos }}" class="rounded text-green-600 focus:ring-green-500">
                                            {{ $pos }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <button type="submit" class="btn-brand-primary w-full justify-center">
                                Commit to Roster
                            </button>
                        </form>
                    </div>
                </div>
            @endcan
        </div>
    </div>

    <!-- TAB 3: ROLES DASHBOARD -->
    <div id="roles-pane" class="tab-content-pane">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2">
                <div class="card-custom">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold">Authorized Team Roles</h3>
                        <span class="text-xs text-gray-500">Assigned staff permissions</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Staff Member</th>
                                    <th>Registered Email</th>
                                    <th>Assigned Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($team->roleAssignments as $assignment)
                                    <tr>
                                        <td>
                                            <div class="font-bold">{{ $assignment->user->name }}</div>
                                            <div class="text-xs text-gray-400">Role granted by Team Admin</div>
                                        </td>
                                        <td>{{ $assignment->user->email }}</td>
                                        <td>
                                            <span class="badge-custom badge-role-{{ str_replace('team_', '', $assignment->role_type) }}">
                                                {{ str_replace('_', ' ', $assignment->role_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            @can('assignRoles', $team)
                                                @if($assignment->user_id !== auth()->id())
                                                    <form method="POST" action="{{ url('/teams/' . $team->id . '/roles/' . $assignment->id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-sm font-bold text-danger-text hover:underline" onclick="return confirm('Revoke this user\'s access to this team?')">
                                                            Revoke Access
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-xs text-gray-400">Cannot Revoke Self</span>
                                                @endif
                                            @else
                                                <span class="text-xs text-gray-400">Locked</span>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-8 text-gray-400">
                                            No assigned staff roles found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Inline Role Assignment Form -->
            @can('assignRoles', $team)
                <div>
                    <div class="card-custom">
                        <h3 class="text-md font-extrabold mb-4 uppercase tracking-wider text-navy">Assign Staff Role</h3>
                        
                        <form method="POST" action="{{ url('/teams/' . $team->id . '/roles') }}">
                            @csrf
                            
                            <div class="mb-4">
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">User UUID *</label>
                                <input type="text" name="user_id" placeholder="Paste target user UUID" required class="input-custom">
                            </div>

                            <div class="mb-6">
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Role Permission Level *</label>
                                <select name="role_type" required class="input-custom">
                                    <option value="coach">Coach (Can score and manage roster)</option>
                                    <option value="scorekeeper">Scorekeeper (Can score games only)</option>
                                    <option value="team_admin">Team Administrator (Full control)</option>
                                    <option value="viewer">Viewer (Read-only access)</option>
                                </select>
                            </div>

                            <button type="submit" class="btn-brand-primary w-full justify-center">
                                Assign Permission Role
                            </button>
                        </form>
                    </div>
                </div>
            @endcan
        </div>
    </div>

</div>

<!-- Vanilla JS Tab-Switching Logic -->
<script>
    function switchTab(event, paneId) {
        // Hide all panes
        document.querySelectorAll('.tab-content-pane').forEach(function(pane) {
            pane.classList.remove('active');
        });

        // Deactivate all tab buttons
        document.querySelectorAll('.tab-btn').forEach(function(btn) {
            btn.classList.remove('active');
        });

        // Show selected pane
        document.getElementById(paneId).classList.add('active');

        // Activate matching tab button
        if (event) {
            event.currentTarget.classList.add('active');
        } else {
            // Find tab button matching target
            const tabButtons = document.querySelectorAll('.tab-btn');
            if (paneId === 'roster-pane') tabButtons[1].classList.add('active');
            if (paneId === 'roles-pane') tabButtons[2].classList.add('active');
        }
    }

    function toggleRosterPathway() {
        const select = document.getElementById('pathway-select');
        const existingFields = document.getElementById('existing-player-fields');
        const newFields = document.getElementById('new-player-fields');

        if (select.value === 'existing') {
            existingFields.style.display = 'block';
            newFields.style.display = 'none';
        } else {
            existingFields.style.display = 'none';
            newFields.style.display = 'block';
        }
    }
</script>
@endsection
