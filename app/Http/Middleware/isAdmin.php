<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
class isAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Not logged in
        if (!Auth::check()) {
            return redirect('/admin/admin-login');
        }

        // Not an admin
        if (!Auth::user()->is_admin) {
            abort(403, 'Admins only');
        }

        return $next($request);
    }
}
