<?php

namespace App\Policies;

use App\Models\DepotRequest;
use App\Models\User;

/**
 * DepotRequestPolicy
 *
 * Définit qui peut faire quoi sur une demande de dépôt (DepotRequest).
 *
 * Rappel : Gate::before() dans AppServiceProvider laisse déjà passer
 * l'admin pour TOUTES les actions. Ces méthodes gèrent donc le cas
 * des users normaux et des gestionnaires.
 */
class DepotRequestPolicy
{
    /**
     * Lister les demandes
     * GET /api/user/depot-requests
     *
     * Tout utilisateur connecté peut lister — mais il ne verra QUE
     * ses propres demandes (le filtrage se fait dans le Controller
     * avec ->where('user_id', Auth::id())).
     *
     * Ici on vérifie juste que l'utilisateur est actif.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Voir une demande précise
     * GET /api/user/depot-requests/{id}
     *
     * Autorisé si :
     *  - c'est le propriétaire de la demande (qui l'a soumise)
     *  - OU c'est le gestionnaire à qui elle est assignée
     *
     * (l'admin passe déjà via Gate::before)
     */
    public function view(User $user, DepotRequest $depotRequest): bool
    {
        // Le user qui a soumis la demande peut la consulter
        if ($user->id === $depotRequest->user_id) {
            return true;
        }

        // Le gestionnaire à qui elle est assignée peut la consulter
        if ($user->isGestionnaire()) {
            return $depotRequest->assignment?->assigned_to === $user->id;
        }

        return false;
    }

    /**
     * Soumettre une nouvelle demande
     * POST /api/user/depot-requests
     *
     * Tout utilisateur actif peut soumettre une demande de dépôt.
     * On exclut volontairement les comptes désactivés.
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Modifier une demande (si on ajoute cette fonctionnalité plus tard)
     *
     * Règle métier : on ne peut modifier sa demande que tant qu'elle
     * n'est pas encore entrée dans le circuit de validation.
     * Une fois assignée à un gestionnaire, le user ne touche plus à rien.
     */
    public function update(User $user, DepotRequest $depotRequest): bool
    {
        return $user->id === $depotRequest->user_id
            && $depotRequest->status === 'pending';
    }

    /**
     * Annuler / supprimer sa propre demande
     *
     * Même règle que update() : uniquement si encore en attente.
     */
    public function delete(User $user, DepotRequest $depotRequest): bool
    {
        return $user->id === $depotRequest->user_id
            && $depotRequest->status === 'pending';
    }
}