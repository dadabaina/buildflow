<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if (!$user->company_id) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Votre compte n\'est associé à aucune entreprise.']);
        }

        if (!$user->company || !$user->company->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Votre entreprise est désactivée. Contactez l\'administrateur.']);
        }

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Votre compte est désactivé.']);
        }

        // Inject company_id in session for CompanyScope
        session(['company_id' => $user->company_id]);

        // Inject company_id for Spatie Permission Teams
        if ($user->company_id) {
            setPermissionsTeamId($user->company_id);
        }

        return $next($request);
    }
}
