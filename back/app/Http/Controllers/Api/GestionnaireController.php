<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentAssignment;
use Illuminate\Support\Facades\Auth;

class GestionnaireController extends Controller
{
    /**
     * LISTER les documents assignés au gestionnaire connecté
     * Route : GET /api/gestionnaire/documents
     *
     * Logique :
     * - On cherche dans document_assignments les lignes où assigned_to = Auth::id()
     * - On charge la demande de dépôt + la référence documentaire + le demandeur
     */
    public function documents()
    {
        $assignments = DocumentAssignment::with([
                'depotRequest.reference.category',  // catégorie du document
                'depotRequest.reference.type',       // type (Thèse, Mémoire...)
                'depotRequest.reference.documents',  // fichiers physiques
                'depotRequest.user:id,name,email',   // utilisateur demandeur
                'assignedBy:id,name',                // admin qui a assigné
            ])
            // Filtrer uniquement les assignations du gestionnaire connecté
            ->where('assigned_to', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($assignments);
    }
}