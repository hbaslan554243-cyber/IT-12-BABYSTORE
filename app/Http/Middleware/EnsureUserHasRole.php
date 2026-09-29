<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Role-based access control (RBAC).
     * Usage in routes: ->middleware('role:owner') or ->middleware('role:owner,staff')
     *
     * Blocks any user whose role is not in the allowed list from reaching
     * the route, and sends them to a friendly "Unauthorized Access" page
     * instead of the module their role isn't permitted to use.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !$user->isActive()) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'username' => 'Your account is inactive or your session has expired. Please contact the store owner.',
            ]);
        }

        if (!in_array($user->role, $roles, true)) {
            return response()->view('errors.unauthorized', [], 403);
        }

        return $next($request);
    }
}
