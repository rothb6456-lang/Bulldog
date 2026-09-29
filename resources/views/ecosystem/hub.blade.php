@extends('layouts.ecosystem')
@section('title', 'Your home base')
@section('content')
@unless(auth()->user()->hasVerifiedEmail())<div class="notice">Welcome aboard. <a href="{{ route('verification.notice') }}">Verify your email</a> to open your apps and protect your account.</div>@endunless
<section class="hero"><div class="eyebrow">One account. Every part of your game.</div><h1>Your next play<br>starts here.</h1><p>Welcome, {{ auth()->user()->name }}. Build your team, put in the work, and see your progress. This is your Bulldog home base.</p>
<div class="coach"><img src="{{ asset('coach.png') }}" alt="Coach"><p><strong>Coach says</strong><br>{{ $sessionCount ? 'Keep showing up. Your next session is ready in Momentum.' : "Haven’t logged a workout yet? Momentum’s ready when you are." }}</p></div></section>
<div class="grid">
<article class="panel product"><div class="product-head"><span class="number">01</span><span class="badge">READY TO PLAY</span></div><h2>Statbook</h2><p>Your roster. Your game. Your season story. Build a team or learn the scorebook with a private practice game.</p><a class="action" href="{{ route('teams.index') }}">Open Statbook →</a></article>
<article class="panel product"><div class="product-head"><span class="number">02</span><span class="badge">READY TO TRAIN</span></div><h2>Momentum</h2><p>Choose a workout, log your sets, and keep moving forward. Your training stays with you—even when the connection doesn’t.</p><form method="post" action="{{ route('momentum.launch') }}">@csrf<button>Launch Momentum →</button></form></article>
<article class="panel product"><div class="product-head"><span class="number">03</span><span class="badge">COMING SOON</span></div><h2>Nutrition</h2><p>Practical fuel for people who train. Explore food-first ideas, straightforward supplement explainers, and recipes for busy training days.</p><a class="action secondary" href="{{ route('nutrition') }}">Explore the preview →</a></article>
<article class="panel product"><div class="product-head"><span class="number">04</span><span class="badge">COMING SOON</span></div><h2>Bulldog gear</h2><p>Wear the work. A first look at the Bulldog collection, built around the same team-first attitude.</p><a class="action secondary" href="{{ route('merch') }}">See the collection concept →</a></article>
</div>
@if($player)<p style="margin-top:24px"><a href="{{ route('players.show', $player) }}">View my player identity and progress →</a></p>@endif
@endsection
