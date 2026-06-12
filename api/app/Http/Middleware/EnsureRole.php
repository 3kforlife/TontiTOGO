<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('http.unauthenticated'),
            ], 401);
        }

        $expected = UserRole::from($role);

        if ($user->role !== $expected) {
            $messageKey = match ($expected) {
                UserRole::Responsible => 'http.forbidden_role_responsible',
                UserRole::Agent       => 'http.forbidden_role_agent',
            };

            return response()->json([
                'success' => false,
                'message' => __($messageKey),
            ], 403);
        }

        return $next($request);
    }
}
