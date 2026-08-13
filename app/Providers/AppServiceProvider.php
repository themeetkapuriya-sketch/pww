<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\ActiveOrderAlertService;
use App\Services\InventoryAlertService;
use App\Services\RolePermissionService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        Blade::directive('inr', function ($expression) {
            return "<?php echo format_indian({$expression}); ?>";
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Dynamically override Mail configuration from DB Settings if present
        try {
            if (Schema::hasTable('settings')) {
                $mailHost = Setting::get('mail_host');
                if (! empty($mailHost)) {
                    $fromAddress = Setting::get('mail_from_address', 'vekariyah@gmail.com');
                    $mailUsername = Setting::get('mail_username');
                    if (empty($mailUsername)) {
                        $mailUsername = $fromAddress;
                    }

                    $encryptedPassword = Setting::get('mail_password');
                    $mailPassword = null;
                    if (! empty($encryptedPassword)) {
                        try {
                            $mailPassword = Crypt::decryptString($encryptedPassword);
                        } catch (DecryptException $e) {
                            // Fallback: password was stored before encryption was added
                            $mailPassword = $encryptedPassword;
                        }
                    }

                    config([
                        'mail.default' => 'smtp',
                        'mail.mailers.smtp.host' => $mailHost,
                        'mail.mailers.smtp.port' => (int) Setting::get('mail_port', 587),
                        'mail.mailers.smtp.username' => $mailUsername,
                        'mail.mailers.smtp.password' => $mailPassword,
                        'mail.mailers.smtp.encryption' => Setting::get('mail_encryption', 'tls'),
                        'mail.from.address' => $fromAddress,
                        'mail.from.name' => Setting::get('mail_from_name', 'Praful Welding Works'),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Ignore during early DB migrations/setup
        }

        // Dynamically override Session Lifetime from DB Settings
        try {
            if (Schema::hasTable('settings')) {
                $sessionTimeout = Setting::get('session_timeout_minutes');
                if (! empty($sessionTimeout) && (int) $sessionTimeout > 0) {
                    config(['session.lifetime' => (int) $sessionTimeout]);
                }
            }
        } catch (\Throwable $e) {
            // Ignore during early DB migrations/setup
        }

        // Register Blade directives for permission and role checking
        Blade::if('hasPermission', function ($permissionKey) {
            return auth()->check() && RolePermissionService::userHasPermission(auth()->user(), $permissionKey);
        });

        Blade::if('hasRole', function ($role) {
            return auth()->check() && auth()->user()->role === $role;
        });

        // Register Gate checks for all system permissions
        foreach (array_keys(RolePermissionService::getPermissionsList()) as $permKey) {
            Gate::define($permKey, function ($user) use ($permKey) {
                return RolePermissionService::userHasPermission($user, $permKey);
            });
        }

        // Define rate limiter for login (5 failed attempts per minute per email + IP)
        RateLimiter::for('login', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return Limit::perMinute(5)
                ->by($email.'|'.$request->ip())
                ->response(function (Request $request, array $headers) {
                    $seconds = $headers['Retry-After'] ?? 60;
                    $message = "Too many login attempts. Please try again in {$seconds} seconds.";

                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => $message,
                            'errors' => [$message],
                        ], 429, $headers);
                    }

                    return redirect()->back()
                        ->withInput($request->only('email'))
                        ->withErrors(['email' => $message]);
                });
        });

        // View Composer for Header to provide Low Stock Alerts and Active Orders dynamically
        View::composer(['layouts.header', 'layouts.header.*', 'layouts.app'], function ($view) {
            $view->with([
                'headerLowStock' => InventoryAlertService::getLowStockSummary(),
                'headerActiveOrders' => ActiveOrderAlertService::getActiveOrdersSummary(),
            ]);
        });
    }
}
