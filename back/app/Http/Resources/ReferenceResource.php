<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferenceResource extends JsonResource
{
    /**
     * Transforme le modèle en tableau JSON envoyé au front Vue.js.
     * Chaque clé ici correspond à ce que tu recevras dans ton JavaScript.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,           // Titre de la référence
            'author'           => $this->author,           // Auteur
            'publisher'        => $this->publisher,        // Éditeur
            'year'             => $this->publication_year, // Année (renommé pour le front)
            'language'         => $this->language,         // Langue

            // Les relations : category et type sont des objets liés
            // On accède à leur "name" grâce au ?-> (null-safe en cas de relation absente)
            'category'         => $this->category?->name,
            'type'             => $this->type?->name,

            'abstract'         => $this->abstract,         // Résumé
            'cover'            => $this->cover_image
                                    // Si une image existe, on construit l'URL complète
                                    ? asset('storage/' . $this->cover_image)
                                    // Sinon, une image par défaut (placeholder)
                                    : null,
            'status'           => $this->status,           // published, pending, etc.
            'downloads'        => 0, // À implémenter plus tard avec un vrai compteur
        ];
    }
}