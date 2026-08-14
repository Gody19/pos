<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureShiftOpen
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->hasOpenShift()) {
            return redirect()
                ->route('shifts.open')
                ->with('error', 'Please open a shift before processing sales.');
        }

        return $next($request);
    }
}