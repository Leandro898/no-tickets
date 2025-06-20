<?php

namespace App\Http\Middleware\Spatie;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role, $guard = null): Response
    {
        if (! $request->user() || ! $request->user()->hasRole($role, $guard)) {
            abort(403);
        }

        return $next($request);
    }
}

