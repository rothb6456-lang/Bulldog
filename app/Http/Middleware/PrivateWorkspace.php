<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PrivateWorkspace
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            abort_unless($request->user()->status === 'active' && ! $request->user()->is_minor, 403, 'This beta is for active adult accounts.');
        }
        $response = $next($request);
        if ($request->user() || $request->is('players/*', 'shared/*', 'guardians/*', 'teams/*')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
            $response->headers->set('Cache-Control', 'private, no-store');
        }
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        return $response;
    }
}
