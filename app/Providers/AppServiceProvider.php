<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') || env('APP_ENV') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Define rate limiter for login (5 failed attempts per minute per email + IP)
        RateLimiter::for('login', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return Limit::perMinute(5)
                ->by($email . '|' . $request->ip())
                ->response(function (Request $request, array $headers) {
                    $seconds = $headers['Retry-After'] ?? 60;
                    $message = "Too many login attempts. Please try again in {$seconds} seconds.";

                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => $message,
                            'errors' => [$message]
                        ], 429, $headers);
                    }

                    return redirect()->back()
                        ->withInput($request->only('email'))
                        ->withErrors(['email' => $message]);
                });
        });
    }
}
