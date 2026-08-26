<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Moderador
{
    /**
     * Deja pasar a Moderador (roles_id = 2) y Admin (roles_id = 3), ya que
     * un Admin siempre puede hacer todo lo que un Moderador puede hacer.
     * Las acciones exclusivas de Admin se protegen además con el
     * middleware "admin" en la ruta correspondiente.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/log_in');
        }

        if (!in_array((int) Auth::user()->roles_id, [2, 3], true)) {
            abort(403);
        }

        return $next($request);
    }
}