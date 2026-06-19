<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * INSCRIPTION
     * Route : POST /api/register
     * Accessible à tous (pas de middleware auth)
     */
    public function register(Request $request)
    {
        // 1. Valider les données envoyées par le formulaire Vue
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users', // email unique en base
            'password' => 'required|string|min:8|confirmed', // confirmed = password_confirmation doit matcher
        ]);

        // 2. Récupérer le rôle "user" par défaut (tout nouvel inscrit est un simple user)
        $roleUser = Role::where('slug', 'user')->firstOrFail();

        // 3. Créer le compte en base de données
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $validated['password'], // sera hashé automatiquement grâce au cast 'hashed'
            'role_id'  => $roleUser->id,
            'is_active'=> true,
        ]);

        // 4. Connecter automatiquement l'utilisateur après inscription (session)
        Auth::login($user);

        // 5. Régénérer la session pour éviter les attaques de fixation de session
        $request->session()->regenerate();

        // 6. Retourner l'utilisateur avec son rôle (eager loading)
        return response()->json([
            'message' => 'Inscription réussie.',
            'user'    => $user->load('role'),
        ], 201);
    }

    /**
     * CONNEXION
     * Route : POST /api/login
     * Accessible à tous
     */
    public function login(Request $request)
    {
        // 1. Valider les champs du formulaire de connexion
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // 2. Tenter l'authentification avec email + mot de passe
        if (!Auth::attempt($request->only('email', 'password'))) {
            // Si échec : on lance une erreur de validation lisible côté Vue
            throw ValidationException::withMessages([
                'email' => ['Ces identifiants ne correspondent à aucun compte.'],
            ]);
        }

        // 3. Vérifier que le compte est actif (pas désactivé par la RH)
        if (!Auth::user()->is_active) {
            Auth::logout(); // On déconnecte immédiatement
            throw ValidationException::withMessages([
                'email' => ['Votre compte est désactivé. Contactez l\'administration.'],
            ]);
        }

        // 4. Régénérer la session (sécurité : fixation de session)
        $request->session()->regenerate();

        // 5. Retourner l'utilisateur connecté avec son rôle
        return response()->json([
            'message' => 'Connexion réussie.',
            'user'    => Auth::user()->load('role'),
        ]);
    }

    /**
     * DÉCONNEXION
     * Route : POST /api/logout
     * Nécessite d'être connecté (middleware auth:sanctum)
     */
    public function logout(Request $request)
    {
        // 1. Déconnecter l'utilisateur de la session
        Auth::logout();

        // 2. Invalider la session côté serveur
        $request->session()->invalidate();

        // 3. Régénérer le token CSRF
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    /**
     * UTILISATEUR CONNECTÉ
     * Route : GET /api/me
     * Retourne l'utilisateur de la session active
     * Utile au rechargement de page pour savoir si on est encore connecté
     */
    public function me(Request $request)
    {
        // Auth::user() retourne null si non connecté, l'user sinon
        return response()->json([
            'user' => $request->user()->load('role'),
        ]);
    }
}