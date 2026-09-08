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
    }

    body {
        background-color: var(--bg);
        color: var(--text);
        font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .brand-headline {
        font-family: 'Impact', 'Arial Black', sans-serif;
        text-transform: uppercase;
        letter-spacing: -0.02em;
    }

    .bg-navy-frame {
        background-color: var(--navy);
    }

    .border-brand {
        border-color: var(--border);
    }

    .text-muted-brand {
        color: var(--text-muted);
    }

    .btn-green-accent {
        background-color: var(--green);
        color: #ffffff;
        font-weight: 700;
        transition: background-color 0.2s ease;
    }

    .btn-green-accent:hover {
        background-color: var(--green-hover);
    }

    .bg-green-soft-accent {
        background-color: var(--green-soft);
        color: var(--green);
    }

    .bg-blue-soft-accent {
        background-color: var(--blue-soft);
        color: var(--blue-accent);
    }

    .badge-admin {
        background-color: var(--navy-2);
        color: #ffffff;
    }

    .badge-coach {
        background-color: var(--blue-soft);
        color: var(--blue-accent);
    }

    .badge-viewer {
        background-color: var(--bg);
        color: var(--text-muted);
    }
</style>

<div class="py-10 px-6 max-w-7xl mx-auto">
    <!-- Top Greeting & Context -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between border-b border-brand pb-6">
        <div>
            <span class="text-xs uppercase font-extrabold tracking-wider" style="color: var(--green);">Team Operations Overview</span>
            <h1 class="text-4xl font-extrabold brand-headline mt-1" style="color: var(--navy);">
                Good morning, {{ $user->profile->display_name ?? $user->name }}.
            </h1>
            <p class="text-sm text-muted-brand mt-1">
                Your current teams, role assignments, and linked identity records are ready to review.
            </p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-3">
            <a href="{{ route('teams.create') }}" class="btn-green-accent px-5 py-2.5 rounded-lg text-sm tracking-wide shadow-sm uppercase inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Create Team
            </a>
            @if($claimedIdentity)
                <a href="{{ route('player.identity', $claimedIdentity->id) }}" class="bg-white border border-brand text-xs font-bold uppercase tracking-wider px-4 py-2.5 rounded-lg inline-flex items-center hover:bg-gray-50 text-slate-800">
                    View Identity
                </a>
            @endif
        </div>
    </div>

    <!-- Stat Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <!-- Card 1: Active Teams -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-brand flex items-center justify-between">
            <div>
                <span class="text-xs uppercase font-extrabold tracking-wider text-muted-brand">Active Teams</span>
                <h3 class="text-4xl font-black mt-2" style="color: var(--navy);">{{ $teamsCount ?? 0 }}</h3>
                <p class="text-xs text-muted-brand mt-1">Across baseball & softball</p>
            </div>
            <div class="p-4 rounded-lg bg-green-soft-accent">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>

        <!-- Card 2: Rostered Players -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-brand flex items-center justify-between">
            <div>
                <span class="text-xs uppercase font-extrabold tracking-wider text-muted-brand">Rostered Players</span>
                <h3 class="text-4xl font-black mt-2" style="color: var(--navy);">{{ $rosteredPlayersCount ?? 0 }}</h3>
                <p class="text-xs text-muted-brand mt-1">Managed under your credentials</p>
            </div>
            <div class="p-4 rounded-lg bg-blue-soft-accent">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
        </div>

        <!-- Card 3: Active Team Roles -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-brand flex items-center justify-between">
            <div>
                <span class="text-xs uppercase font-extrabold tracking-wider text-muted-brand">Active Team Roles</span>
                <h3 class="text-4xl font-black mt-2" style="color: var(--navy);">{{ $activeRolesCount ?? 0 }}</h3>
                <p class="text-xs text-muted-brand mt-1">Contextual permissions active</p>
            </div>
            <div class="p-4 rounded-lg bg-slate-100 text-slate-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
        </div>
    </div>

    <!-- Active Team Context Panel (Main Operational Block) -->
    @if($primaryTeam)
    <div class="bg-white rounded-xl shadow-sm border border-brand overflow-hidden mb-10">
        <div class="bg-navy-frame px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="text-xs uppercase font-extrabold tracking-wider" style="color: var(--green);">Active Team Context</span>
                <h2 class="text-2xl font-extrabold text-white brand-headline mt-1">{{ $primaryTeam->name }}</h2>
            </div>
            <div class="mt-2 sm:mt-0 flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full badge-admin">
                    {{ $primaryRole ?? 'Team Admin' }}
                </span>
                <span class="text-xs text-slate-400 font-semibold">
                    {{ $primaryTeam->sport->name }} · {{ $primaryTeam->season_label }}
                </span>
            </div>
        </div>
        <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between bg-slate-50 border-t border-brand">
            <p class="text-sm text-muted-brand leading-relaxed max-w-2xl">
                Roster structure, player codes, and coaching roles are integrated into one working view for this context. 
                Manage game events or updates safely from your core workspace.
            </p>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('teams.show', $primaryTeam->id) }}" class="btn-green-accent px-5 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider shadow-sm inline-flex items-center">
                    Open Team Workspace →
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Secondary Split Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: My Teams List (Assigned Contexts) -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-brand overflow-hidden">
            <div class="px-6 py-5 border-b border-brand flex items-center justify-between">
                <h3 class="text-lg font-bold brand-headline" style="color: var(--navy);">Assigned Team Contexts</h3>
                <a href="{{ route('teams.index') }}" class="text-xs font-bold uppercase tracking-wider hover:underline" style="color: var(--blue-accent);">
                    View All Teams →
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-xs font-extrabold uppercase tracking-wider text-muted-brand border-b border-brand">
                            <th class="px-6 py-4">Team</th>
                            <th class="px-6 py-4">Season</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand">
                        @forelse($teams as $team)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <a href="{{ route('teams.show', $team->id) }}" class="font-bold hover:underline" style="color: var(--navy);">
                                        {{ $team->name }}
                                    </a>
                                    <div class="text-xs text-muted-brand mt-0.5">
                                        {{ $team->sport->name }} · {{ $team->age_group ?? 'All Ages' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-muted-brand">
                                    {{ $team->season_label }}
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $userRole = $team->roleAssignments->firstWhere('user_id', $user->id)->role_type ?? 'viewer';
                                    @endphp
                                    <span class="text-[10px] font-extrabold uppercase tracking-widest px-2.5 py-1 rounded-full 
                                        @if($userRole === 'team_admin') badge-admin 
                                        @elseif($userRole === 'coach') badge-coach 
                                        @else badge-viewer @endif">
                                        {{ str_replace('_', ' ', $userRole) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        {{ ucfirst($team->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-muted-brand">
                                    No team contexts assigned. Get started by creating your first team!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Actions & Identity Summary -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-brand p-6">
                <h3 class="text-lg font-bold brand-headline mb-4 border-b border-brand pb-3" style="color: var(--navy);">Quick Actions</h3>
                <ul class="space-y-3.5">
                    <li>
                        <a href="{{ route('teams.create') }}" class="flex items-center text-sm font-bold hover:underline group" style="color: var(--navy);">
                            <span class="w-2 h-2 rounded-full mr-3 group-hover:scale-125 transition-transform" style="background-color: var(--green);"></span>
                            Create a new team
                        </a>
                    </li>
                    @if($primaryTeam)
                    <li>
                        <a href="{{ route('teams.show', $primaryTeam->id) }}#roster" class="flex items-center text-sm font-bold hover:underline group" style="color: var(--navy);">
                            <span class="w-2 h-2 rounded-full mr-3 group-hover:scale-125 transition-transform" style="background-color: var(--blue-accent);"></span>
                            Review {{ $primaryTeam->name }} roster
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('teams.show', $primaryTeam->id) }}#roles" class="flex items-center text-sm font-bold hover:underline group" style="color: var(--navy);">
                            <span class="w-2 h-2 rounded-full mr-3 group-hover:scale-125 transition-transform" style="background-color: var(--navy-2);"></span>
                            Manage team roles
                        </a>
                    </li>
                    @endif
                    <li>
                        <a href="{{ route('guardians.index') }}" class="flex items-center text-sm font-bold hover:underline group" style="color: var(--navy);">
                            <span class="w-2 h-2 rounded-full mr-3 group-hover:scale-125 transition-transform" style="background-color: var(--text-muted);"></span>
                            Manage guardian links
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Identity Box -->
            <div class="bg-white rounded-xl shadow-sm border border-brand p-6">
                <h3 class="text-lg font-bold brand-headline mb-4 border-b border-brand pb-3" style="color: var(--navy);">Linked Identity</h3>
                @if($claimedIdentity)
                    <div class="p-4 rounded-lg bg-green-soft-accent mb-3">
                        <span class="text-xs font-black tracking-widest uppercase block">{{ $claimedIdentity->player_code }}</span>
                        <span class="text-sm font-bold block mt-1" style="color: var(--navy);">{{ $claimedIdentity->display_name }}</span>
                        <span class="text-[10px] uppercase font-extrabold tracking-wider text-emerald-800 mt-1 inline-block">Claimed & Active</span>
                    </div>
                    <p class="text-xs text-muted-brand leading-relaxed">
                        Your permanent athlete profile is linked successfully. Statistics generated under this identity will persist across teams and life stages.
                    </p>
                @else
                    <div class="p-4 rounded-lg bg-amber-50 border border-amber-200 mb-3">
                        <span class="text-xs font-extrabold tracking-wider text-amber-800 uppercase block">No Player Identity Linked</span>
                        <p class="text-xs text-amber-700 mt-1 leading-relaxed">
                            You have not claimed an athletic identity record yet. Claiming your player profile compiles your permanent sports history automatically.
                        </p>
                    </div>
                    <a href="{{ route('player.claim') }}" class="text-xs font-bold uppercase tracking-wider hover:underline inline-flex items-center" style="color: var(--blue-accent);">
                        Claim Your Profile Now →
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
