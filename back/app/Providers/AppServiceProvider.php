<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Models\DepotRequest; 
use App\Policies\DepotRequestPolicy;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        /**
         * Enregistrement des Policies
         *
         * Laravel associe automatiquement App\Models\User → App\Policies\UserPolicy
         * si les noms correspondent, mais on l'explicite ici pour la clarté.
         *
         * Gate::policy(Modèle::class, Policy::class)
         * Signifie : "Pour toute action sur User, utilise UserPolicy"
         */
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(DepotRequest::class, DepotRequestPolicy::class);

        /**
         * Super-admin Gate (optionnel mais très pratique)
         *
         * "before" s'exécute AVANT toutes les vérifications de policy.
         * Si l'utilisateur connecté est admin ET actif → il passe partout
         * sans avoir besoin de vérifier chaque méthode une par une.
         *
         * Retourner null (ne rien retourner) = laisser la policy décider normalement.
         */
        Gate::before(function (User $user, string $ability) {
            // Un admin inactif ne doit pas passer
            if ($user->isAdmin() && $user->is_active) {
                return true; // court-circuite toutes les policies pour l'admin
            }
            // Pour les autres rôles → on laisse la policy décider
            return null;
        });
    }
}