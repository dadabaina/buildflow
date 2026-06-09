<?php

if (! function_exists('currentCompany')) {
    /**
     * Retourne la société de l'utilisateur authentifié.
     */
    function currentCompany(): \App\Models\Company
    {
        return \Illuminate\Support\Facades\Auth::user()->company;
    }
}
