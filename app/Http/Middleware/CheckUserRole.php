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
        $userRole = auth()->user()->roles->first()?->name;

        // Superadmin can access all panels
        if ($userRole === 'superadmin') {
            return $next($request);
        }

        // Admin can only access employee panel
        if ($role === 'admin' && $userRole === 'admin') {
            return $next($request);
        }

        // User role can only access user panel
        if ($role === 'user' && $userRole === 'user') {
            return $next($request);
        }

        // Redirect to appropriate panel based on role
        if ($userRole === 'admin') {
            return redirect('/employee');
        } else {
            return redirect('/user');
        }
    }
}
