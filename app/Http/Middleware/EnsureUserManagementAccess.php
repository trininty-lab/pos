<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserManagementAccess
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if ($user?->isSuperAdmin() || $user?->hasRole('owner') || $user?->can($permission)) {
            return $next($request);
        }

        abort(403);
    }
}