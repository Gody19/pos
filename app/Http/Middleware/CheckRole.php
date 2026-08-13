<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (in_array($user->role_name, $roles, true)) {
            return $next($request);
        }

        return response()->json([
            'message' => 'You do not have permission to perform this action.',
        ], 403);
    }
}
