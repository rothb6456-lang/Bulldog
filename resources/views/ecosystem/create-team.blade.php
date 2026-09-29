@extends('layouts.ecosystem')
@section('title', 'Create your team')
@section('content')
<div class="eyebrow">Statbook / New team</div><h1>Build your dugout.</h1><div class="grid"><form class="panel stack" method="post" action="{{ route('teams.store') }}">@csrf
<label for="name">Team name</label><input id="name" name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="organization">
<label for="sport_id">Sport</label><select id="sport_id" name="sport_id" required>@foreach($sports as $sport)<option value="{{ $sport->id }}">{{ $sport->name }}</option>@endforeach</select>
<label for="season_label">Season</label><input id="season_label" name="season_label" value="{{ old('season_label') }}" placeholder="Fall {{ date('Y') }}" maxlength="50">
<label for="age_group">Age group</label><input id="age_group" name="age_group" value="{{ old('age_group') }}" placeholder="Adult, 12U, Varsity…" maxlength="50"><button>Create team</button></form>
<aside class="panel"><h2>A clear starting lineup.</h2><p>You’ll be this team’s administrator and coach. Add players next, then schedule a game.</p><p class="quiet">Youth player identities stay private and unclaimed. Guardians must accept an invitation and receive administrator confirmation before access is granted.</p></aside></div>
@endsection
