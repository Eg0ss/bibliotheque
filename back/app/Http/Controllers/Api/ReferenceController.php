<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReferenceResource;
use App\Models\ConsultationLog;
use App\Models\DocumentReference;
use Illuminate\Http\Request;

class ReferenceController extends Controller
{
    /**
     * Liste toutes les références PUBLIÉES avec recherche et filtres.
     * URL : GET /api/references
     *
     * Paramètres acceptés dans la query string :
     *   ?search=mot       → filtre par titre OU auteur
     *   ?category_id=2    → filtre par catégorie
     *   ?type_id=3        → filtre par type (Thèse, Mémoire...)
     *   ?per_page=12      → nombre de résultats par page (défaut: 12)
     */
    public function index(Request $request)
    {
        // On commence la requête sur les références publiées uniquement
        $query = DocumentReference::where('status', 'published')
            ->with(['category', 'type']); // charge les relations pour l'affichage

        // ── Filtre recherche texte ────────────────────────────────────────────
        // Si l'utilisateur a tapé quelque chose dans la barre de recherche
        if ($search = $request->get('search')) {
            // LIKE '%terme%' = contient le terme, insensible à la casse (MySQL)
            $query->where(function ($q) use ($search) {
                $q->where('title',  'LIKE', "%{$search}%")
                  ->orWhere('author', 'LIKE', "%{$search}%");
            });
        }

        // ── Filtre par catégorie ──────────────────────────────────────────────
        // Si une catégorie est sélectionnée dans le filtre
        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // ── Filtre par type (Thèse, Mémoire, Article...) ─────────────────────
        if ($typeId = $request->get('type_id')) {
            $query->where('type_id', $typeId);
        }

        // ── Tri et pagination ─────────────────────────────────────────────────
        // latest() = ORDER BY created_at DESC (plus récents en premier)
        // paginate() = retourne { data: [...], total, per_page, current_page... }
        $perPage = $request->get('per_page', 12); // 12 par défaut
        $references = $query->latest()->paginate($perPage);

        // ReferenceResource::collection formate chaque référence proprement
        return ReferenceResource::collection($references);
    }

    /**
     * Détail d'une référence — inchangé
     */
    public function show(Request $request, string $id)
    {
        $reference = DocumentReference::with(['category', 'type'])
            ->findOrFail($id);

        ConsultationLog::create([
            'user_id'      => $request->user()?->id,
            'reference_id' => $reference->id,
        ]);

        $reference->increment('views_count');

        return new ReferenceResource($reference->fresh(['category', 'type']));
    }
}