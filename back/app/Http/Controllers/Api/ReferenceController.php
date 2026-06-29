<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReferenceResource;
use App\Models\DocumentReference;

use Illuminate\Http\Request;

class ReferenceController extends Controller
{
    /**
     * Liste toutes les références PUBLIÉES.
     * Cette route sera publique (accessible sans connexion).
     *
     * URL : GET /api/references
     */
    public function index()
    {
        // On récupère uniquement les références dont le statut est 'published'
        // ->with(['category', 'type']) charge les relations en une seule requête SQL
        //   (évite le problème N+1 : sans ça, Laravel ferait 1 requête par référence)
        // ->latest() trie par date de création décroissante (les plus récentes en premier)
        $references = DocumentReference::where('status', 'published')
            ->with(['category', 'type'])
            ->latest()
            ->get();

        // ReferenceResource::collection() transforme chaque modèle
        // en utilisant le format défini dans ReferenceResource::toArray()
        return ReferenceResource::collection($references);
    }

    /**
     * Détail d'une référence spécifique.
     * URL : GET /api/references/{id}
     *
     * @param string $id  L'identifiant de la référence dans l'URL
     */
    public function show(string $id)
    {
        // findOrFail() cherche par ID et retourne automatiquement une erreur 404
        // si la référence n'existe pas (au lieu de planter avec une erreur PHP)
        $reference = DocumentReference::with(['category', 'type'])
            ->findOrFail($id);

        // Retourne UNE seule ressource (pas une collection)
        return new ReferenceResource($reference);
    }
}