<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Qui peut faire cette requête ?
     * true = tout utilisateur authentifié (on gérera les droits dans le contrôleur)
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation pour la création d'un compte
     * Laravel vérifie ces règles automatiquement avant d'entrer dans le contrôleur
     */
    public function rules(): array
{
    return [
        'name'      => 'required|string|max:255',
        'email'     => 'required|email|unique:users,email',
        'password'  => 'required|string|min:8|confirmed',
        'role_id'   => 'required|exists:roles,id',
        // L'admin choisit explicitement le statut à la création
        // 'boolean' accepte : true/false, 1/0, "1"/"0"
        'is_active' => 'required|boolean',
    ];
}

public function messages(): array
{
    return [
        'name.required'      => 'Le nom est obligatoire.',
        'email.required'     => 'L\'adresse e-mail est obligatoire.',
        'email.unique'       => 'Cette adresse e-mail est déjà utilisée.',
        'password.required'  => 'Le mot de passe est obligatoire.',
        'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
        'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        'role_id.required'   => 'Le rôle est obligatoire.',
        'role_id.exists'     => 'Le rôle sélectionné est invalide.',
        'is_active.required' => 'Le statut du compte est obligatoire.',
    ];
}
}