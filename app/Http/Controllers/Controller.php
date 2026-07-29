<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Restreint l'accès à un chantier pour le rôle "chef_chantier" à ses seuls
     * chantiers assignés (table project_managers). Les autres rôles gardent
     * l'accès société existant (déjà filtré par CompanyScope) : cette méthode
     * ne fait rien pour eux.
     */
    protected function authorizeProjectScope(int $projectId): void
    {
        $user = Auth::user();
        if ($user->hasRole('chef_chantier')) {
            abort_unless($user->managesProject($projectId), 403);
        }
    }
}
