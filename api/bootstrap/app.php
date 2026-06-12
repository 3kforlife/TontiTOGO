<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // CORS doit être le premier middleware pour toutes les requêtes
        $middleware->prepend([
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'role'             => \App\Http\Middleware\EnsureRole::class,
            'verified'         => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'password.changed' => \App\Http\Middleware\EnsurePasswordChanged::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // ModelNotFoundException → 404 JSON propre en français
        $exceptions->render(function (
            \Illuminate\Database\Eloquent\ModelNotFoundException $e,
            Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => __('http.not_found'),
                ], 404);
            }
        });

        // AuthenticationException → 401 JSON
        $exceptions->render(function (
            \Illuminate\Auth\AuthenticationException $e,
            Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => __('http.unauthenticated'),
                ], 401);
            }
        });

        // AuthorizationException → 403 JSON
        $exceptions->render(function (
            \Illuminate\Auth\Access\AuthorizationException $e,
            Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => __('http.unauthorized'),
                ], 403);
            }
        });
    })->create();
