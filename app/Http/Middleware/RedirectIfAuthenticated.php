<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        if ($request->user()) {
            return redirect($this->redirectPath($request));
        }

        return $next($request);
    }

    protected function redirectPath(Request $request): string
    {
        $role = $request->user()?->role?->slug;

        return match ($role) {
            'admin' => route('admin.dashboard'),
            'lsg_officer' => route('lsg.dashboard'),
            'officer' => route('officer.dashboard'),
            default => route('student.dashboard'),
        };
    }
}
