<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            ! $request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
             ! $request->user()->hasVerifiedEmail())
        ) {
            return response()->json([
                'success' => false,
                'message' => __('http.email_not_verified'),
                'action'  => 'email_not_verified',
            ], 403);
        }

        return $next($request);
    }
}
