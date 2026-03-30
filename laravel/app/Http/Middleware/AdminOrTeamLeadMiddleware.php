<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrTeamLeadMiddleware
{
    /**
     * Handle an incoming request.
     * Only admins and team leads can access the route.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || (!$user->isAdmin() && !$user->isTeamLead())) {
            abort(403);
        }

        return $next($request);
    }
}
