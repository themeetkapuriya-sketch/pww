<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest' || $request->header('X-PWW-SPA') === '1') {
                return response()->json([
                    'message' => 'Session expired. Redirecting to login...',
                    'redirect' => route('login'),
                ], 419);
            }

            return redirect()->route('login')->with('error', 'Your session has expired. Please login again.');
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest' || $request->header('X-PWW-SPA') === '1') {
                return response()->json([
                    'message' => 'Unauthenticated. Redirecting to login...',
                    'redirect' => route('login'),
                ], 401);
            }

            return redirect()->route('login');
        });
    })->create();
