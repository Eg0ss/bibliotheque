<?php

namespace App\Http\Requests\DepotRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepotRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Tout utilisateur connecté peut soumettre une demande
        return true;
    }

    public function rules(): array
    {
        return [
            // ── Métadonnées obligatoires ──────────────────────────────
            'title'            => ['required', 'string', 'max:255'],
            'author'           => ['required', 'string', 'max:255'],
            'category_id'      => ['required', 'integer', 'exists:categories,id'],
            'type_id'          => ['required', 'integer', 'exists:types,id'],

            // ── Métadonnées optionnelles ──────────────────────────────
            'publisher'        => ['nullable', 'string', 'max:255'],
            'publication_year' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'language'         => ['nullable', 'string', 'max:10'],
            'isbn'             => ['nullable', 'string', 'max:20'],
            'abstract'         => ['nullable', 'string', 'max:3000'],

            // ── Fichier PDF ───────────────────────────────
            'file'             => ['nullable', 'file', 'mimes:pdf', 'max:20480'], // 20 Mo max

            // ── Image de couverture optionnelle ───────────────────────
            'cover_image'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'Le titre est obligatoire.',
            'author.required'      => 'L\'auteur est obligatoire.',
            'category_id.required' => 'Veuillez sélectionner une catégorie.',
            'category_id.exists'   => 'La catégorie sélectionnée n\'existe pas.',
            'type_id.required'     => 'Veuillez sélectionner un type de document.',
            'type_id.exists'       => 'Le type sélectionné n\'existe pas.',
            'file.required'        => 'Le fichier PDF est optionnel.',
            'file.mimes'           => 'Le fichier doit être au format PDF.',
            'file.max'             => 'Le fichier ne doit pas dépasser 20 Mo.',
            'cover_image.image'    => 'La couverture doit être une image.',
            'cover_image.max'      => 'L\'image ne doit pas dépasser 2 Mo.',
            'publication_year.max' => 'L\'année ne peut pas être dans le futur.',
        ];
    }
}