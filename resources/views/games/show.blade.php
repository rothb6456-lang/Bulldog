@extends('layouts.app')

@section('content')
<div class="game-workspace" style="--bg: #f3f5f4; --surface: #ffffff; --navy: #081722; --green: #007a43; --green-hover: #00663a; --border: #d7dede; --text-muted: #5f6d78; --blue-accent: #0f6f97; --blue-soft: #e7f2f7;">
    
    <!-- Hero Header Banner -->
    <div class="hero-banner" style="background: linear-gradient(135deg, var(--navy) 0%, #102533 100%); color: white; padding: 2.5rem; border-radius: var(--radius-lg, 20px) var(--radius-lg, 20px) 0 0; border-bottom: 4px solid var(--green); margin-bottom: 2rem;">
        <div class="flex-container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
            <div>
                <span class="badge" style="background: var(--green); color: white; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em;">
                    {{ strtoupper($game->status) }}
                </span>
                <h1 style="font-size: 2.25rem; font-weight: 800; margin: 0.5rem 0 0.25rem 0; font-family: sans-serif; letter-spacing: -0.02em;">
                    {{ $game->awayTeam->name }} @ {{ $game->homeTeam->name }}
                </h1>
                <p style="margin: 0; color: #a1b0cb; font-size: 1rem; font-weight: 500;">
                    {{ $game->sport->name }} &bull; {{ $game->ruleset->name }} &bull; Scheduled: {{ $game->scheduled_at ? $game->scheduled_at->format('M d, Y - g:i A') : 'TBD' }}
                </p>
            </div>
            
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="{{ url('/teams/' . $game->home_team_id) }}" class="btn-secondary" style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 0.75rem 1.5rem; border-radius: var(--radius-sm, 10px); font-weight: bold; text-decoration: none; transition: background 0.2s;">
                    Back to Team
                </a>
                @can('manageRoster', $game->homeTeam)
                    <form action="{{ url('/games/' . $game->id . '/start') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn-primary" style="background: var(--green); border: none; color: white; padding: 0.75rem 1.5rem; border-radius: var(--radius-sm, 10px); font-weight: bold; cursor: pointer; transition: background 0.2s;">
                            Start Live Play
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>

    <!-- Alert Messaging -->
    @if(session('success'))
        <div style="background: #e4f3eb; border: 1px solid #c2e3d2; color: var(--green); padding: 1rem; border-radius: var(--radius-sm, 10px); margin-bottom: 1.5rem; font-weight: 600;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Workspace Tabs Layout -->
    <div class="workspace-tabs" style="display: flex; border-bottom: 2px solid var(--border); margin-bottom: 2rem; gap: 1.5rem;">
        <button class="tab-btn active" onclick="switchWorkspaceTab(event, 'lineup-tab')" style="background: none; border: none; border-bottom: 4px solid var(--green); color: var(--navy); padding: 1rem 0.5rem; font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: all 0.2s;">
            1. Batting Order (Lineup)
        </button>
        <button class="tab-btn" onclick="switchWorkspaceTab(event, 'defense-tab')" style="background: none; border: none; border-bottom: 4px solid transparent; color: var(--text-muted); padding: 1rem 0.5rem; font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: all 0.2s;">
            2. Defensive Positions
        </button>
        <button class="tab-btn" onclick="switchWorkspaceTab(event, 'roster-tab')" style="background: none; border: none; border-bottom: 4px solid transparent; color: var(--text-muted); padding: 1rem 0.5rem; font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: all 0.2s;">
            3. Game Eligibility
        </button>
    </div>

    <!-- TAB 1: BATTING ORDER / LINEUP SETUP -->
    <div id="lineup-tab" class="tab-content" style="display: block;">
        <div class="grid-container" style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            
            <!-- Active Batting Order Card -->
            <div class="card" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md, 16px); padding: 1.5rem; box-shadow: var(--shadow-sm, 0 1px 2px rgba(8, 23, 34, 0.06));">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 style="font-size: 1.5rem; font-weight: bold; color: var(--navy); margin: 0;">Active Batting Order</h2>
                    <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 600;">Continuous Batting: {{ $game->ruleset->config_json['continuous_batting_order'] ? 'ON' : 'OFF' }}</span>
                </div>

                <form action="{{ url('/games/' . $game->id . '/lineup') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--border);">
                                    <th style="padding: 0.75rem 0.5rem; color: var(--text-muted); font-weight: bold; width: 60px;">Slot</th>
                                    <th style="padding: 0.75rem 0.5rem; color: var(--text-muted); font-weight: bold;">Player</th>
                                    <th style="padding: 0.75rem 0.5rem; color: var(--text-muted); font-weight: bold; width: 100px;">Jersey</th>
                                    <th style="padding: 0.75rem 0.5rem; color: var(--text-muted); font-weight: bold; width: 150px;">Batting Pos</th>
                                    <th style="padding: 0.75rem 0.5rem; color: var(--text-muted); font-weight: bold; width: 120px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="lineup-tbody">
                                @forelse($lineupEntries as $index => $entry)
                                    <tr style="border-bottom: 1px solid var(--border); transition: background 0.15s;" class="lineup-row">
                                        <td style="padding: 1rem 0.5rem;">
                                            <input type="number" name="slots[{{ $entry->id }}][order]" value="{{ $entry->batting_order_slot }}" style="width: 50px; padding: 0.4rem; border: 1px solid var(--border); border-radius: 6px; text-align: center; font-weight: bold;" min="1" max="25">
                                        </td>
                                        <td style="padding: 1rem 0.5rem; font-weight: 700; color: var(--navy);">
                                            {{ $entry->playerIdentity->display_name }}
                                            <span style="display: block; font-size: 0.8rem; color: var(--text-muted); font-weight: normal;">{{ $entry->playerIdentity->player_code }}</span>
                                        </td>
                                        <td style="padding: 1rem 0.5rem; font-weight: bold; color: var(--blue-accent);">
                                            #{{ $entry->jersey_number ?? 'N/A' }}
                                        </td>
                                        <td style="padding: 1rem 0.5rem;">
                                            <select name="slots[{{ $entry->id }}][lineup_status]" style="padding: 0.4rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.9rem; background: var(--surface);">
                                                <option value="starter" {{ $entry->lineup_status == 'starter' ? 'selected' : '' }}>Starter</option>
                                                <option value="substitute" {{ $entry->lineup_status == 'substitute' ? 'selected' : '' }}>Substitute</option>
                                            </select>
                                        </td>
                                        <td style="padding: 1rem 0.5rem; text-align: center;">
                                            <button type="button" onclick="moveRowUp(this)" style="background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 1.1rem; padding: 0.2rem;" title="Move Up">▲</button>
                                            <button type="button" onclick="moveRowDown(this)" style="background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 1.1rem; padding: 0.2rem;" title="Move Down">▼</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="padding: 2.5rem 0.5rem; text-align: center; color: var(--text-muted); font-weight: 600;">
                                            No players placed in the active batting order yet. Add players below or select "Load Team Roster" inside Game Eligibility.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($lineupEntries->isNotEmpty())
                        <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn-primary" style="background: var(--green); color: white; border: none; padding: 0.75rem 2rem; border-radius: var(--radius-sm, 10px); font-weight: bold; cursor: pointer;">
                                Save Batting Order
                            </button>
                        </div>
                    @endif
                </form>
            </div>

            <!-- Add Available Players Sidebar -->
            <div class="card" style="background: var(--surface-alt, #f7f8f8); border: 1px solid var(--border); border-radius: var(--radius-md, 16px); padding: 1.5rem;">
                <h3 style="font-size: 1.25rem; font-weight: bold; color: var(--navy); margin-top: 0; margin-bottom: 1rem;">Available Bench</h3>
                <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.4;">
                    These rostered players are eligible to play but are not currently in the active batting lineup.
                </p>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @forelse($benchPlayers as $bench)
                        <div style="background: var(--surface); border: 1px solid var(--border); padding: 1rem; border-radius: var(--radius-sm, 10px); display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow-sm);">
                            <div>
                                <span style="font-weight: 700; color: var(--navy); display: block;">{{ $bench->playerIdentity->display_name }}</span>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">#{{ $bench->jersey_number ?? 'N/A' }} &bull; {{ $bench->playerIdentity->player_code }}</span>
                            </div>
                            <form action="{{ url('/games/' . $game->id . '/lineup/add') }}" method="POST" style="margin: 0;">
                                @csrf
                                <input type="hidden" name="player_identity_id" value="{{ $bench->player_identity_id }}">
                                <button type="submit" style="background: var(--blue-soft); color: var(--blue-accent); border: none; padding: 0.4rem 0.8rem; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 0.85rem; transition: background 0.2s;">
                                    + Add
                                </button>
                            </form>
                        </div>
                    @empty
                        <div style="text-align: center; color: var(--text-muted); padding: 1.5rem; font-size: 0.9rem; font-weight: 600;">
                            No players on the bench. All eligible players are actively rostered in the lineup!
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: DEFENSIVE POSITIONS -->
    <div id="defense-tab" class="tab-content" style="display: none;">
        <div class="grid-container" style="display: grid; grid-template-columns: 1.2fr 1.8fr; gap: 2rem; align-items: start;">
            
            <!-- Defensive Assignment Form -->
            <div class="card" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md, 16px); padding: 1.5rem; box-shadow: var(--shadow-sm);">
                <h2 style="font-size: 1.5rem; font-weight: bold; color: var(--navy); margin-top: 0; margin-bottom: 1.5rem;">Field Positions Assignment</h2>
                
                <form action="{{ url('/games/' . $game->id . '/defense') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                        @foreach(['P' => 'Pitcher (1)', 'C' => 'Catcher (2)', '1B' => 'First Base (3)', '2B' => 'Second Base (4)', '3B' => 'Third Base (5)', 'SS' => 'Shortstop (6)', 'LF' => 'Left Field (7)', 'CF' => 'Center Field (8)', 'RF' => 'Right Field (9)', 'DP_FLEX' => 'DP / Flex Slot'] as $posCode => $posLabel)
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; border-bottom: 1px solid var(--surface-alt, #f7f8f8); padding-bottom: 0.75rem;">
                                <label style="font-weight: 700; color: var(--navy); min-width: 140px; font-size: 0.95rem;">{{ $posLabel }}</label>
                                <select name="defense[{{ $posCode }}]" onchange="updateDiamondVisualization('{{ $posCode }}', this.options[this.selectedIndex].text)" style="flex-grow: 1; max-width: 250px; padding: 0.5rem; border: 1px solid var(--border); border-radius: var(--radius-sm, 10px); background: var(--surface);">
                                    <option value="">-- Vacant --</option>
                                    @foreach($lineupEntries as $player)
                                        <option value="{{ $player->player_identity_id }}" {{ (isset($defensiveAssignments[$posCode]) && $defensiveAssignments[$posCode] == $player->player_identity_id) ? 'selected' : '' }}>
                                            #{{ $player->jersey_number ?? '?' }} - {{ $player->playerIdentity->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>

                    <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn-primary" style="background: var(--green); color: white; border: none; padding: 0.75rem 2rem; border-radius: var(--radius-sm, 10px); font-weight: bold; cursor: pointer;">
                            Save Field Positions
                        </button>
                    </div>
                </form>
            </div>

            <!-- Field Diamond Visual Blueprint -->
            <div class="card" style="background: var(--navy); border: 1px solid var(--navy-2, #102533); border-radius: var(--radius-md, 16px); padding: 2rem; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; overflow: hidden; min-height: 580px;">
                <div style="color: rgba(255, 255, 255, 0.4); text-transform: uppercase; font-size: 0.8rem; font-weight: bold; letter-spacing: 0.15em; position: absolute; top: 1.5rem; left: 1.5rem;">Defensive Diamond Blueprint</div>
                
                <!-- Simple Mock CSS Diamond Field Representation -->
                <div class="field-diamond" style="position: relative; width: 420px; height: 420px; border: 3px dashed rgba(255, 255, 255, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; background: radial-gradient(circle, rgba(0, 122, 67, 0.2) 0%, rgba(8, 23, 34, 0) 70%);">
                    
                    <!-- Outfielders -->
                    <div id="vis-LF" class="field-node" style="position: absolute; top: 10%; left: 15%;" data-label="Left Field">
                        <span class="vis-badge" style="background: var(--blue-accent); color: white; font-weight: bold; font-size: 0.75rem; padding: 0.35rem 0.75rem; border-radius: 8px; border: 1px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3); display: block; text-align: center; min-width: 90px;">
                            LF: Vacant
                        </span>
                    </div>
                    <div id="vis-CF" class="field-node" style="position: absolute; top: 5%; left: 40%;" data-label="Center Field">
                        <span class="vis-badge" style="background: var(--blue-accent); color: white; font-weight: bold; font-size: 0.75rem; padding: 0.35rem 0.75rem; border-radius: 8px; border: 1px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3); display: block; text-align: center; min-width: 90px;">
                            CF: Vacant
                        </span>
                    </div>
                    <div id="vis-RF" class="field-node" style="position: absolute; top: 10%; right: 15%;" data-label="Right Field">
                        <span class="vis-badge" style="background: var(--blue-accent); color: white; font-weight: bold; font-size: 0.75rem; padding: 0.35rem 0.75rem; border-radius: 8px; border: 1px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3); display: block; text-align: center; min-width: 90px;">
                            RF: Vacant
                        </span>
                    </div>

                    <!-- Infielders -->
                    <div id="vis-SS" class="field-node" style="position: absolute; top: 32%; left: 25%;" data-label="Shortstop">
                        <span class="vis-badge" style="background: var(--green); color: white; font-weight: bold; font-size: 0.75rem; padding: 0.35rem 0.75rem; border-radius: 8px; border: 1px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3); display: block; text-align: center; min-width: 90px;">
                            SS: Vacant
                        </span>
                    </div>
                    <div id="vis-2B" class="field-node" style="position: absolute; top: 32%; right: 25%;" data-label="Second Base">
                        <span class="vis-badge" style="background: var(--green); color: white; font-weight: bold; font-size: 0.75rem; padding: 0.35rem 0.75rem; border-radius: 8px; border: 1px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3); display: block; text-align: center; min-width: 90px;">
                            2B: Vacant
                        </span>
                    </div>
                    <div id="vis-3B" class="field-node" style="position: absolute; top: 52%; left: 12%;" data-label="Third Base">
                        <span class="vis-badge" style="background: var(--green); color: white; font-weight: bold; font-size: 0.75rem; padding: 0.35rem 0.75rem; border-radius: 8px; border: 1px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3); display: block; text-align: center; min-width: 90px;">
                            3B: Vacant
                        </span>
                    </div>
                    <div id="vis-1B" class="field-node" style="position: absolute; top: 52%; right: 12%;" data-label="First Base">
                        <span class="vis-badge" style="background: var(--green); color: white; font-weight: bold; font-size: 0.75rem; padding: 0.35rem 0.75rem; border-radius: 8px; border: 1px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3); display: block; text-align: center; min-width: 90px;">
                            1B: Vacant
                        </span>
                    </div>

                    <!-- Pitcher & Catcher -->
                    <div id="vis-P" class="field-node" style="position: absolute; top: 50%; left: 40%;" data-label="Pitcher">
                        <span class="vis-badge" style="background: #a30000; color: white; font-weight: bold; font-size: 0.75rem; padding: 0.35rem 0.75rem; border-radius: 8px; border: 1px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3); display: block; text-align: center; min-width: 90px;">
                            P: Vacant
                        </span>
                    </div>
                    <div id="vis-C" class="field-node" style="position: absolute; bottom: 8%; left: 40%;" data-label="Catcher">
                        <span class="vis-badge" style="background: #e67e22; color: white; font-weight: bold; font-size: 0.75rem; padding: 0.35rem 0.75rem; border-radius: 8px; border: 1px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3); display: block; text-align: center; min-width: 90px;">
                            C: Vacant
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 3: GAME ELIGIBILITY & ROSTER ACTIONS -->
    <div id="roster-tab" class="tab-content" style="display: none;">
        <div class="card" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md, 16px); padding: 2rem; box-shadow: var(--shadow-sm);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 style="font-size: 1.5rem; font-weight: bold; color: var(--navy); margin: 0;">Game Roster Eligibility</h2>
                    <p style="margin: 0.25rem 0 0 0; color: var(--text-muted); font-size: 0.9rem;">
                        Check which players from the core roster are physically present and cleared to participate in this game.
                    </p>
                </div>
                
                <form action="{{ url('/games/' . $game->id . '/roster/load-from-team') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-secondary" style="background: var(--blue-soft); color: var(--blue-accent); border: 1px solid var(--border); padding: 0.6rem 1.25rem; border-radius: var(--radius-sm, 10px); font-weight: bold; cursor: pointer; transition: all 0.2s;">
                        🔄 Load Active Team Roster
                    </button>
                </form>
            </div>

            <form action="{{ url('/games/' . $game->id . '/roster') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div style="overflow-x: auto; margin-bottom: 2rem;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border); background: var(--surface-alt);">
                                <th style="padding: 1rem; color: var(--navy); font-weight: bold; width: 100px; text-align: center;">Cleared</th>
                                <th style="padding: 1rem; color: var(--navy); font-weight: bold;">Roster Player</th>
                                <th style="padding: 1rem; color: var(--navy); font-weight: bold;">Jersey #</th>
                                <th style="padding: 1rem; color: var(--navy); font-weight: bold;">Roster Role</th>
                                <th style="padding: 1rem; color: var(--navy); font-weight: bold;">Claim Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($gameRosterEntries as $rosterPlayer)
                                <tr style="border-bottom: 1px solid var(--border); transition: background 0.15s;">
                                    <td style="padding: 1rem; text-align: center;">
                                        <input type="checkbox" name="eligible_players[]" value="{{ $rosterPlayer->player_identity_id }}" {{ $rosterPlayer->eligible_to_play ? 'checked' : '' }} style="width: 1.25rem; height: 1.25rem; accent-color: var(--green);">
                                    </td>
                                    <td style="padding: 1rem; font-weight: bold; color: var(--navy);">
                                        {{ $rosterPlayer->playerIdentity->display_name }}
                                        <span style="display: block; font-size: 0.8rem; color: var(--text-muted); font-weight: normal;">{{ $rosterPlayer->playerIdentity->player_code }}</span>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <input type="text" name="jersey[{{ $rosterPlayer->player_identity_id }}]" value="{{ $rosterPlayer->jersey_number }}" style="width: 60px; padding: 0.4rem; border: 1px solid var(--border); border-radius: 6px;" placeholder="--">
                                    </td>
                                    <td style="padding: 1rem; color: var(--text-muted); font-weight: 600;">
                                        Player
                                    </td>
                                    <td style="padding: 1rem;">
                                        <span class="badge" style="padding: 0.25rem 0.5rem; border-radius: 6px; font-size: 0.75rem; font-weight: bold; 
                                            @if($rosterPlayer->playerIdentity->claim_status == 'claimed') background: var(--green-soft); color: var(--green); 
                                            @else background: #fff2d9; color: var(--warning-text); @endif">
                                            {{ strtoupper($rosterPlayer->playerIdentity->claim_status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="padding: 3rem 1rem; text-align: center; color: var(--text-muted); font-weight: 600;">
                                        No team roster members loaded. Click "Load Active Team Roster" to copy your active players into this matchup session.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($gameRosterEntries->isNotEmpty())
                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn-primary" style="background: var(--green); color: white; border: none; padding: 0.75rem 2.5rem; border-radius: var(--radius-sm, 10px); font-weight: bold; cursor: pointer;">
                            Apply Eligibility & Update Jerseys
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>

</div>

<!-- Vanilla JS Tab Swapping & Dynamic Blueprint Node Render -->
<script>
    function switchWorkspaceTab(event, tabId) {
        // Toggle tab content display
        const contents = document.getElementsByClassName('tab-content');
        for (let i = 0; i < contents.length; i++) {
            contents[i].style.display = 'none';
        }
        document.getElementById(tabId).style.display = 'block';

        // Toggle button active states
        const buttons = document.getElementsByClassName('tab-btn');
        for (let i = 0; i < buttons.length; i++) {
            buttons[i].classList.remove('active');
            buttons[i].style.borderBottom = '4px solid transparent';
            buttons[i].style.color = 'var(--text-muted)';
        }
        event.currentTarget.classList.add('active');
        event.currentTarget.style.borderBottom = '4px solid var(--green)';
        event.currentTarget.style.color = 'var(--navy)';
    }

    function moveRowUp(button) {
        const row = button.closest('tr');
        const prev = row.previousElementSibling;
        if (prev) {
            row.parentNode.insertBefore(row, prev);
            recalculateSlotNumbers();
        }
    }

    function moveRowDown(button) {
        const row = button.closest('tr');
        const next = row.nextElementSibling;
        if (next) {
            row.parentNode.insertBefore(next, row);
            recalculateSlotNumbers();
        }
    }

    function recalculateSlotNumbers() {
        const rows = document.querySelectorAll('#lineup-tbody .lineup-row');
        rows.forEach((row, index) => {
            const input = row.querySelector('input[type="number"]');
            if (input) {
                input.value = index + 1;
            }
        });
    }

    function updateDiamondVisualization(posCode, selectionText) {
        const element = document.getElementById('vis-' + posCode);
        if (!element) return;
        
        const badge = element.querySelector('.vis-badge');
        if (selectionText.includes('-- Vacant --') || selectionText.trim() === '') {
            badge.innerText = posCode + ': Vacant';
            badge.style.opacity = '0.4';
        } else {
            // Strip out the jersey and display the name
            badge.innerText = posCode + ': ' + selectionText.split('-')[1].trim();
            badge.style.opacity = '1';
        }
    }

    // Initialize visualization on page load
    document.addEventListener('DOMContentLoaded', function() {
        const selectors = document.querySelectorAll('select[name^="defense"]');
        selectors.forEach(select => {
            const posCode = select.name.match(/\[(.*?)\]/)[1];
            updateDiamondVisualization(posCode, select.options[select.selectedIndex].text);
        });
    });
</script>
@endsection
