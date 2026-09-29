@extends('layouts.ecosystem')
@section('title', 'Private player profile')
@section('content')
<section class="hero"><div class="eyebrow">Private player identity</div><h1>{{ $player->display_name }}</h1><p>Athletic progress, connected. This profile is visible only to authorized accounts.</p></section>
<div class="grid"><section class="panel"><h2>Career snapshot</h2>@forelse($stats as $stat)<p><strong>{{ $stat->stat_key }}: {{ $stat->stat_value }}</strong> <span class="quiet">{{ $stat->source_type }}</span></p>@empty<p>No finalized game stats yet. Practice games never count here.</p>@endforelse</section><section class="panel"><h2>Team memberships</h2>@foreach($player->memberships as $membership)<p>{{ $membership->team->name }} · #{{ $membership->jersey_number ?: '—' }} · {{ $membership->status }}</p>@endforeach</section>
@can('viewTraining', $player)<section class="panel"><h2>Momentum</h2>@if($player->trainingProfile)<p>Experience: {{ $player->trainingProfile->experience_level }}</p>@foreach($player->trainingGoals->where('status','active') as $goal)<p>{{ $goal->title }}</p>@endforeach @else<p>Your training profile will appear here after you connect and sync it from Momentum.</p>@endif @if($player->user_id === auth()->id())<form method="post" action="{{ route('momentum.launch') }}">@csrf<button>Launch Momentum →</button></form>@endif</section>@endcan</div>
@endsection
