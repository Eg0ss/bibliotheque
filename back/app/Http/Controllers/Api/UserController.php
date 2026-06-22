<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;  // ← on l'utilise maintenant
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
        $user = User::create([
            'name'      => $request->validated()['name'],
            'email'     => $request->validated()['email'],
            'password'  => $request->validated()['password'],
            'role_id'   => $request->validated()['role_id'],
            'is_active' => true,
        ]);

        $user->load('role');

        return response()->json([
            'message' => 'Compte créé avec succès.',
            'user'    => new UserResource($user),
        ], 201);
    }

    /**
     * VOIR un utilisateur précis
     * Route : GET /api/admin/users/{user}
     * Utilisé par UserShowView.vue au chargement de la page
     */
    public function show(string $user)
    {
        // findOrFail lance automatiquement une erreur 404 si l'id n'existe pas
        $foundUser = User::with('role')->findOrFail($user);

        return new UserResource($foundUser);
    }

    /**
     * MODIFIER un compte utilisateur
     * Route : PUT /api/admin/users/{user}
     * Utilisé par le formulaire dans UserShowView.vue
     *
     * UpdateUserRequest valide automatiquement les données AVANT d'entrer ici
     */
    public function update(UpdateUserRequest $request, string $user)
    {
        // On récupère le user (404 automatique si inexistant)
        $foundUser = User::findOrFail($user);

        // On prépare les données à mettre à jour
        // array_filter supprime les valeurs null/vides pour ne pas écraser inutilement
        $data = [
            'name'    => $request->validated()['name'],
            'email'   => $request->validated()['email'],
            'role_id' => $request->validated()['role_id'],
        ];

        // Le mot de passe est optionnel : on ne le met à jour que s'il est fourni
        if (!empty($request->validated()['password'])) {
            $data['password'] = $request->validated()['password'];
            // Pas besoin de hasher manuellement : le cast 'hashed' dans User.php s'en charge
        }

        // Mise à jour en base de données
        $foundUser->update($data);

        // On recharge les relations pour la réponse JSON
        $foundUser->load('role');

        return response()->json([
            'message' => 'Compte modifié avec succès.',
            'user'    => new UserResource($foundUser),
        ]);
    }

    /**
     * ACTIVER / DÉSACTIVER un compte
     * Route : PATCH /api/admin/users/{id}/toggle-status
     * Le "toggle" inverse simplement la valeur actuelle de is_active
     */
    public function toggleStatus(string $id)
    {
        $user = User::findOrFail($id);

        // ! inverse le booléen : true → false, false → true
        $user->update(['is_active' => !$user->is_active]);

        $statusLabel = $user->is_active ? 'activé' : 'désactivé';

        return response()->json([
            'message'   => "Compte {$statusLabel} avec succès.",
            'is_active' => $user->is_active,
        ]);
    }

    /**
     * SUPPRIMER un compte
     * Route : DELETE /api/admin/users/{id}
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        // Sécurité : on empêche un admin de se supprimer lui-même
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'Vous ne pouvez pas supprimer votre propre compte.',
            ], 403); // 403 = Forbidden
        }

        $user->delete();

        return response()->json([
            'message' => 'Compte supprimé avec succès.',
        ]);
    }

    /**
     * LISTER les rôles disponibles
     * Route : GET /api/admin/roles
     * Utilisé par le <select> des formulaires
     */
    public function getRoles()
    {
        $roles = Role::orderBy('name')->get(['id', 'name', 'slug']);

        return response()->json([
            'data' => $roles
        ]);
    }
}