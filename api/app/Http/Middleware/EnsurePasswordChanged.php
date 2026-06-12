<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            if (! $request->routeIs('agent.password.change')) {
                return response()->json([
                    'success' => false,
                    'message' => __('http.must_change_password'),
                    'action'  => 'must_change_password',
                ], 403);
            }
        }

        return $next($request);
    }
}
