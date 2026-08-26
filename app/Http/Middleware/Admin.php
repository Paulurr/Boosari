<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    /**
     * Solo deja pasar a usuarios autenticados con rol Admin (roles_id = 3).
     * Se usa para acciones exclusivas de administración: crear usuarios,
     * gestionar configuraciones globales, etc.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/log_in');
        }

        if ((int) Auth::user()->roles_id !== 3) {
            abort(403);
        }

        return $next($request);
    }
}