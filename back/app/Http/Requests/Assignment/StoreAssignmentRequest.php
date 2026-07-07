<?php

namespace App\Http\Requests\Assignment;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
{
    /**
     * Autorisation.
     *
     * La vérification que seul un administrateur
     * puisse assigner une demande sera effectuée
     * par les Policies.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation.
     */
    public function rules(): array
    {
        return [

            // Demande à assigner
            'depot_request_id' => [
                'required',
                'exists:depot_requests,id'
            ],

            // Gestionnaire choisi
            'assigned_to' => [
                'required',
                'exists:users,id'
            ],

            // Date limite choisie par l'administrateur
            'due_date' => [
                'required',
                'date',
                'after_or_equal:today'
            ],

            // Instructions facultatives
            'instructions' => [
                'nullable',
                'string',
                'max:1000'
            ],

        ];
    }

    /**
     * Messages personnalisés.
     */
    public function messages(): array
    {
        return [

            'depot_request_id.required' =>
                'Veuillez sélectionner une demande.',

            'depot_request_id.exists' =>
                'Cette demande n\'existe pas.',

            'assigned_to.required' =>
                'Veuillez choisir un gestionnaire.',

            'assigned_to.exists' =>
                'Le gestionnaire sélectionné est introuvable.',

            'due_date.required' =>
                'Veuillez choisir une date limite.',

            'due_date.date' =>
                'La date limite est invalide.',

            'due_date.after_or_equal' =>
                'La date limite ne peut pas être dans le passé.',

            'instructions.max' =>
                'Les instructions ne doivent pas dépasser 1000 caractères.',
        ];
    }
}