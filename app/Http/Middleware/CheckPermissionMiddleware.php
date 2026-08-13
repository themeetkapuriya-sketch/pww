<?php

namespace App\Http\Middleware;

use App\Services\RolePermissionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permissionKey): Response
    {
        $user = Auth::user();

        if (! RolePermissionService::userHasPermission($user, $permissionKey)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Denied: You do not have permission to perform this action. Contact your Administrator.',
                ], 403);
            }

            return redirect()->route('overview')->with('error', 'Access Denied: You do not have permission to access that feature.');
        }

        return $next($request);
    }
}
