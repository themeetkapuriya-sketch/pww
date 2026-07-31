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
        if (file_exists(app_path('helpers.php'))) {
            require_once app_path('helpers.php');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Blade::directive('inr', function ($expression) {
            return "<?php echo format_indian({$expression}); ?>";
        });

        if ($this->app->environment('production') || env('APP_ENV') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Dynamically override Mail configuration from DB Settings if present
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $mailHost = \App\Models\Setting::get('mail_host');
                if (!empty($mailHost)) {
                    $fromAddress = \App\Models\Setting::get('mail_from_address', 'vekariyah@gmail.com');
                    $mailUsername = \App\Models\Setting::get('mail_username');
                    if (empty($mailUsername)) {
                        $mailUsername = $fromAddress;
                    }

                    config([
                        'mail.default' => 'smtp',
                        'mail.mailers.smtp.host' => $mailHost,
                        'mail.mailers.smtp.port' => (int) \App\Models\Setting::get('mail_port', 587),
                        'mail.mailers.smtp.username' => $mailUsername,
                        'mail.mailers.smtp.password' => \App\Models\Setting::get('mail_password'),
                        'mail.mailers.smtp.encryption' => \App\Models\Setting::get('mail_encryption', 'tls'),
                        'mail.from.address' => $fromAddress,
                        'mail.from.name' => \App\Models\Setting::get('mail_from_name', 'Praful Welding Works'),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Ignore during early DB migrations/setup
        }

        // Register Blade directives for permission and role checking
        \Illuminate\Support\Facades\Blade::if('hasPermission', function ($permissionKey) {
            return auth()->check() && \App\Services\RolePermissionService::userHasPermission(auth()->user(), $permissionKey);
        });

        \Illuminate\Support\Facades\Blade::if('hasRole', function ($role) {
            return auth()->check() && auth()->user()->role === $role;
        });

        // Register Gate checks for all system permissions
        foreach (array_keys(\App\Services\RolePermissionService::getPermissionsList()) as $permKey) {
            \Illuminate\Support\Facades\Gate::define($permKey, function ($user) use ($permKey) {
                return \App\Services\RolePermissionService::userHasPermission($user, $permKey);
            });
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
