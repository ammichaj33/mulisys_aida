<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Si plusieurs rôles sont passés, vérifier si l'utilisateur a au moins un des rôles
        if (count($roles) > 1) {
            if (!auth()->user()->hasAnyRole($roles)) {
                abort(403, 'Accès non autorisé.');
            }
        } else {
            // Si un seul rôle est passé, vérifier ce rôle spécifique
            if (!auth()->user()->hasRole($roles[0])) {
                abort(403, 'Accès non autorisé.');
            }
        }

        return $next($request);
    }
}
