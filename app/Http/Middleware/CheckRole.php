<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next): Response
    // {
    //     return $next($request);
    // }

    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized');
        }

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

        if (empty($allowed) || ! in_array($user->role, $allowed, true)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
