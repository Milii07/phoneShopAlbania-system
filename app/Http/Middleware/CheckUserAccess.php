<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Reuse centralised permissions helper.
        $routeName = $request->route()->getName();

        if (function_exists('user_can_access_route') && user_can_access_route($routeName)) {
            return $next($request);
        }

        abort(403, 'You do not have permission to access this page.');
    }
}
