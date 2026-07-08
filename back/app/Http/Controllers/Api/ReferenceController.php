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
     * Liste toutes les références PUBLIÉES.
     * URL : GET /api/references
     */
    public function index(Request $request)
    {
        $references = DocumentReference::where('status', 'published')
            ->with(['category', 'type'])
            ->latest()
            ->get();

        return ReferenceResource::collection($references);
    }

    /**
     * Détail d'une référence spécifique.
     * URL : GET /api/references/{id}
     *
     * Cette route est PUBLIQUE (pas de middleware auth:sanctum) :
     * un visiteur non connecté peut la consulter.
     * Mais si un cookie de session valide existe, $request->user()
     * retournera quand même l'utilisateur connecté (grâce à statefulApi()).
     */
    public function show(Request $request, string $id)
    {
        $reference = DocumentReference::with(['category', 'type'])
            ->findOrFail($id);

        // ── Comptabilisation de la vue ───────────────────────────────
        // On enregistre qui a consulté (ou null si visiteur anonyme),
        // puis on incrémente le compteur. Ceci se produit à CHAQUE clic,
        // pour n'importe quel utilisateur, connecté ou non.
        ConsultationLog::create([
            'user_id'      => $request->user()?->id,
            'reference_id' => $reference->id,
        ]);

        $reference->increment('views_count');

        return new ReferenceResource($reference->fresh(['category', 'type']));
    }
}