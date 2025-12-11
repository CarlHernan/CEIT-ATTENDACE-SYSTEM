<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  string[]  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // If no user or user doesn't have the required role, redirect to login
        if (! $user || ! $user->role?->slug || ! in_array($user->role->slug, $roles, true)) {
            // Log out the user and redirect to login
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'You do not have permission to access that page.');
        }

        return $next($request);
    }
}
