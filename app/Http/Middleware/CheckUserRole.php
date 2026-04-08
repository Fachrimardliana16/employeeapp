<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
        // User sudah dipastikan login oleh Authenticate middleware
        $user = auth()->user();
        
        // Prevent access if not verified
        if (!$user->is_verified) {
            auth()->logout();
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

        // Check for permission
        if ($user->hasPermissionTo($permission)) {
            return $next($request);
        }

        // Redirect to appropriate panel based on the FIRST available permission
        if ($user->hasPermissionTo('access_admin_panel')) {
            return redirect('/admin');
        } elseif ($user->hasPermissionTo('access_employee_panel')) {
            return redirect('/employee');
        } elseif ($user->hasPermissionTo('access_user_panel')) {
            return redirect('/user');
        }

        auth()->logout();
        return redirect('/admin/login')->withErrors([
            'email' => 'You do not have permission to access any panel.',
        ]);
    }
}
