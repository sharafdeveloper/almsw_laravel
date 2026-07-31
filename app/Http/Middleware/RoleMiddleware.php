<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage in routes: ->middleware('role:admin') or ->middleware('role:1,2')
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  mixed  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized');
        }

        // Normalize roles: allow names or numeric IDs
        $allowed = [];
        foreach ($roles as $r) {
            if (is_numeric($r)) {
                $allowed[] = (int) $r;
            } else {
                $r = strtolower(trim($r));
                if ($r === 'admin') {
                    $allowed[] = \App\Models\User::ROLE_ADMIN;
                } elseif ($r === 'employee') {
                    $allowed[] = \App\Models\User::ROLE_EMPLOYEE;
                }
            }
        }

        if (empty($allowed)) {
            // No roles provided, deny by default
            abort(403, 'Unauthorized');
        }

        if (! in_array($user->role, $allowed, true)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
