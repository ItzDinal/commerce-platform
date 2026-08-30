<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()
                ->route('admin.login')
                ->with('error', 'Please login to access the admin portal.');
        }

        if (! $request->user()->isActiveAdmin()) {
            abort(403, 'You are not authorized to access the admin portal.');
        }

        return $next($request);
    }
}