<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class Usuario
{
    /**
     * Handle an incoming request.
     *
     * Este middleware es el "portón" general para cualquier usuario
     * autenticado, sin importar su rol (Usuario, Moderador o Admin).
     *
     * OJO: antes esto exigía `roles_id == 1`, lo que en la práctica
     * bloqueaba con un 403 a los Admin/Moderador cuando intentaban
     * entrar a /home, /profile, /config, etc. La restricción por rol
     * para secciones específicas ahora vive en los middlewares
     * "admin" y "moderador", que se aplican solo donde corresponde.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/log_in');
        }

        return $next($request);
    }
}