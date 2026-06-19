<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * LISTER tous les utilisateurs
     * Route : GET /api/admin/users
     * Utilisé par la page UsersListView.vue
     */
    public function index()
    {
        // On charge les utilisateurs avec leur rôle (eager loading)
        // paginate(15) = 15 par page, Laravel génère les infos de pagination automatiquement
        $users = User::with('role')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // UserResource::collection formate chaque user via UserResource
        return UserResource::collection($users);
    }

    /**
     * CRÉER un nouveau compte utilisateur
     * Route : POST /api/admin/users
     * Utilisé par le formulaire UserCreateView.vue
     */
    public function store(StoreUserRequest $request)
    {
        // Les données sont déjà validées par StoreUserRequest
        // $request->validated() retourne uniquement les champs validés (sécurité)
        $user = User::create([
            'name'      => $request->validated()['name'],
            'email'     => $request->validated()['email'],
            'password'  => $request->validated()['password'], // hashé auto via cast 'hashed'
            'role_id'   => $request->validated()['role_id'],
            'is_active' => true, // actif par défaut à la création
        ]);

        // On recharge avec le rôle pour la réponse JSON complète
        $user->load('role');

        return response()->json([
            'message' => 'Compte créé avec succès.',
            'user'    => new UserResource($user),
        ], 201); // 201 = Created
    }

    /**
     * VOIR un utilisateur précis
     * Route : GET /api/admin/users/{id}
     */
    public function show(string $id)
    {
        // findOrFail lance automatiquement une erreur 404 si l'id n'existe pas
        $user = User::with('role')->findOrFail($id);

        return new UserResource($user);
    }

    /**
     * MODIFIER un compte utilisateur
     * Route : PUT /api/admin/users/{id}
     * (On implémentera cette méthode à la prochaine étape)
     */
    public function update(Request $request, string $id)
    {
        // À implémenter
    }

    /**
     * ACTIVER / DÉSACTIVER un compte
     * Route : PATCH /api/admin/users/{id}/toggle-status
     * (On implémentera cette méthode à la prochaine étape)
     */
    public function toggleStatus(string $id)
    {
        // À implémenter
    }

    /**
     * SUPPRIMER un compte
     * Route : DELETE /api/admin/users/{id}
     * (On implémentera cette méthode à la prochaine étape)
     */
    public function destroy(string $id)
    {
        // À implémenter
    }
}