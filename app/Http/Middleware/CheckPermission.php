<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): mixed
    {

        if (!auth()->check()) {
            return redirect()->route('login');
        }


        if (!auth()->user()->can($permission)) {
            abort(403, 'Nuk keni akses në këtë faqe.');
        }

        return $next($request);
    }
}
