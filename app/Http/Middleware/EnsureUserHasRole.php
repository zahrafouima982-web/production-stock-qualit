<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route middleware: role:ADMIN or role:ADMIN,PRODUCTION_MANAGER (comma-separated, any-of).
 *
 * Assumed to run AFTER the built-in 'auth' middleware (see routes/web.php), so an
 * unauthenticated request never reaches here in normal use. The explicit checks
 * below are defensive and also cover the "deactivated mid-session" case.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'You must be logged in to access this page.');
        }

        if (! $user->is_active) {
            // Account was deactivated after the session started — force logout.
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Your account has been deactivated. Please contact an administrator.');
        }

        $userRoleName = $user->role?->name;

        if (! $userRoleName || ! in_array($userRoleName, $roles, true)) {
            abort(403, 'You do not have permission to access this module.');
        }

        return $next($request);
    }
}
