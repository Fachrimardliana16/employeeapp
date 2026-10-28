<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = auth()->user();

        if (! $user || ! $user->is_active || ! $user->is_verified) {
            Auth::logout();
            return redirect('/admin/login')->withErrors([
                'email' => 'Your account is not verified yet. Please contact the administrator.',
            ]);
        }

        // Map middleware role parameter to permission names
        $permissionMap = [
            'superadmin' => 'access_admin_panel',
            'admin'      => 'access_employee_panel',
            'user'       => 'access_user_panel',
        ];

        $permission = $permissionMap[$role] ?? "access_{$role}_panel";

        if ($user->hasPermissionTo($permission)) {
            return $next($request);
        }

        $panelRedirects = [
            'access_admin_panel' => '/admin',
            'access_employee_panel' => '/employee',
            'access_user_panel' => '/user',
        ];

        foreach ($panelRedirects as $permissionName => $path) {
            if ($user->hasPermissionTo($permissionName)) {
                return redirect($path);
            }
        }

        Auth::logout();
        return redirect('/admin/login')->withErrors([
            'email' => 'You do not have permission to access any panel.',
        ]);
    }
}
