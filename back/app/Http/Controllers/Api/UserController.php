<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * LISTER les utilisateurs
     * GET /api/admin/users
     *
     * $this->authorize('viewAny', User::class)
     * → appelle UserPolicy::viewAny($userConnecte)
     * → Gate::before laisse passer l'admin, bloque les autres → 403
     */
    public function index(Request $request)
    {
        // Vérification de droit : seul l'admin peut lister tous les users
        $this->authorize('viewAny', User::class);

        $query = User::with('role')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', (bool) $request->status);
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        $users = $query->paginate(15)->appends($request->query());

        return UserResource::collection($users);
    }

    /**
     * CRÉER un utilisateur
     * POST /api/admin/users
     *
     * authorize('create', User::class)
     * → UserPolicy::create($userConnecte)
     * → Gate::before laisse passer l'admin
     */
    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

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
     * VOIR un utilisateur
     * GET /api/admin/users/{user}
     *
     * authorize('view', $foundUser)
     * → UserPolicy::view($userConnecte, $foundUser)
     * → Admin : Gate::before → OK
     * → Autre : OK seulement si c'est son propre profil
     */
    public function show(string $user)
    {
        $foundUser = User::with('role')->findOrFail($user);

        $this->authorize('view', $foundUser);

        return new UserResource($foundUser);
    }

    /**
     * MODIFIER un utilisateur
     * PUT /api/admin/users/{user}
     *
     * authorize('update', $foundUser)
     * → UserPolicy::update($userConnecte, $foundUser)
     */
    public function update(UpdateUserRequest $request, string $user)
    {
        $foundUser = User::findOrFail($user);

        $this->authorize('update', $foundUser);

        $data = [
            'name'    => $request->validated()['name'],
            'email'   => $request->validated()['email'],
            'role_id' => $request->validated()['role_id'],
        ];

        if (!empty($request->validated()['password'])) {
            $data['password'] = $request->validated()['password'];
        }

        $foundUser->update($data);
        $foundUser->load('role');

        return response()->json([
            'message' => 'Compte modifié avec succès.',
            'user'    => new UserResource($foundUser),
        ]);
    }

    /**
     * ACTIVER / DÉSACTIVER
     * PATCH /api/admin/users/{id}/toggle-status
     *
     * authorize('toggleStatus', $user)
     * → UserPolicy::toggleStatus($userConnecte, $user)
     * → Gate::before laisse passer l'admin
     */
    public function toggleStatus(string $id)
    {
        $user = User::findOrFail($id);

        $this->authorize('toggleStatus', $user);

        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'Vous ne pouvez pas modifier le statut de votre propre compte.',
            ], 403);
        }

        $user->update(['is_active' => !$user->is_active]);
        $statusLabel = $user->is_active ? 'activé' : 'désactivé';

        return response()->json([
            'message'   => "Compte {$statusLabel} avec succès.",
            'is_active' => $user->is_active,
        ]);
    }

    /**
     * SUPPRIMER un utilisateur
     * DELETE /api/admin/users/{id}
     *
     * authorize('delete', $user)
     * → UserPolicy::delete($userConnecte, $user)
     * → Gate::before laisse passer l'admin
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'Vous ne pouvez pas supprimer votre propre compte.',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'Compte supprimé avec succès.',
        ]);
    }

    /**
     * LISTE DES RÔLES
     * GET /api/admin/roles
     */
    public function getRoles()
    {
        $roles = Role::orderBy('name')->get(['id', 'name', 'slug']);
        return response()->json(['data' => $roles]);
    }
}