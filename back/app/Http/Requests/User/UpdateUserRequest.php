<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Qui peut utiliser cette requête ?
     * true = tout utilisateur authentifié (le guard de route s'occupe déjà du reste)
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation pour la modification d'un utilisateur
     * Ces règles sont vérifiées automatiquement AVANT d'entrer dans le Controller
     */
    public function rules(): array
    {
        // On récupère l'id du user depuis l'URL (/api/admin/users/{id})
        // pour pouvoir ignorer son propre email dans la règle "unique"
        $userId = $this->route('user');

        return [
            // Nom obligatoire, entre 2 et 100 caractères
            'name'    => ['required', 'string', 'min:2', 'max:100'],

            // Email obligatoire, format valide, unique SAUF pour ce user lui-même
            'email'   => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],

            // Rôle obligatoire, doit exister dans la table roles
            'role_id' => ['required', 'integer', 'exists:roles,id'],

            // Mot de passe OPTIONNEL (on ne force pas à rechanger)
            // S'il est fourni : minimum 8 caractères + confirmation obligatoire
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Messages d'erreur personnalisés en français
     */
    public function messages(): array
    {
        return [
            'name.required'    => 'Le nom est obligatoire.',
            'name.min'         => 'Le nom doit contenir au moins 2 caractères.',
            'email.required'   => 'L\'adresse e-mail est obligatoire.',
            'email.email'      => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique'     => 'Cette adresse e-mail est déjà utilisée.',
            'role_id.required' => 'Le rôle est obligatoire.',
            'role_id.exists'   => 'Le rôle sélectionné n\'existe pas.',
            'password.min'     => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed'=> 'La confirmation du mot de passe ne correspond pas.',
        ];
    }
}