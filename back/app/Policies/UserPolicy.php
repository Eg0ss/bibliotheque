<?php

namespace App\Policies;

use App\Models\User;

/**
 * UserPolicy
 *
 * Définit qui peut faire quoi sur le modèle User.
 *
 * Laravel appelle automatiquement la bonne méthode selon l'action :
 *   viewAny → lister tous les users
 *   view    → voir un user précis
 *   create  → créer un user
 *   update  → modifier un user
 *   delete  → supprimer un user
 *
 * Paramètres :
 *   $user  = l'utilisateur CONNECTÉ (celui qui fait l'action)
 *   $model = l'utilisateur CIBLÉ (celui sur lequel on agit) — absent pour viewAny/create
 *
 * Note : l'admin est déjà autorisé pour tout via Gate::before() dans AppServiceProvider.
 * Ces méthodes ne s'appliquent donc qu'aux autres rôles.
 */
class UserPolicy
{
    /**
     * Lister tous les utilisateurs
     * GET /api/admin/users
     *
     * Seul l'admin peut voir la liste complète.
     * (Gate::before laisse passer l'admin automatiquement)
     * Pour tous les autres → false.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Voir le profil d'un utilisateur précis
     * GET /api/admin/users/{id}
     *
     * L'admin peut voir n'importe quel profil.
     * Un user peut voir son propre profil.
     * Les autres → non.
     */
    public function view(User $user, User $model): bool
    {
        // $user->id === $model->id signifie "je consulte mon propre profil"
        return $user->id === $model->id;
    }

    /**
     * Créer un compte utilisateur
     * POST /api/admin/users
     *
     * Seul l'admin peut créer des comptes via le backoffice.
     */
    public function create(User $user): bool
    {
        return false; // Gate::before laisse passer l'admin
    }

    /**
     * Modifier un compte utilisateur
     * PUT /api/admin/users/{id}
     *
     * L'admin peut modifier n'importe qui.
     * Un user peut modifier son propre profil (via /api/profile — route séparée).
     */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    /**
     * Supprimer un compte utilisateur
     * DELETE /api/admin/users/{id}
     *
     * Seul l'admin peut supprimer.
     * Et même l'admin ne peut pas se supprimer lui-même
     * (cette règle est dans le controller, la policy autorise le reste).
     */
    public function delete(User $user, User $model): bool
    {
        return false; // Gate::before laisse passer l'admin
    }

    /**
     * Activer / désactiver un compte
     * PATCH /api/admin/users/{id}/toggle-status
     *
     * Seul l'admin peut changer le statut.
     * La règle "ne pas se désactiver soi-même" reste dans le controller.
     */
    public function toggleStatus(User $user, User $model): bool
    {
        return false; // Gate::before laisse passer l'admin
    }
}