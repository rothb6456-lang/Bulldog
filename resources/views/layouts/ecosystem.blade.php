<!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow"><meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Your home base') · Bulldog</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
<link rel="stylesheet" href="{{ asset('ecosystem.css') }}">
</head><body>
<a class="skip" href="#main">Skip to content</a>
<header class="topbar"><a class="brand" href="{{ route('dashboard') }}"><img src="{{ asset('coach.png') }}" alt="">BULLDOG <span>YOUR HOME BASE</span></a>
<nav aria-label="Main navigation"><a href="{{ route('dashboard') }}">Hub</a><a href="{{ route('teams.index') }}">Statbook</a><a href="{{ route('guardians.index') }}">Guardians</a><a href="{{ route('profile.edit') }}">Account</a><form action="{{ route('logout') }}" method="post">@csrf<button class="text-button">Sign out</button></form></nav></header>
<main id="main" class="workspace">
@if(session('success'))<div class="notice" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="notice error" role="alert"><strong>Please check these details.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')
</main><footer class="footer"><strong>BULLDOG</strong><span>Built for the next play.</span><a href="https://bulldogstats.com">Back to Bulldog</a></footer>
</body></html>
