<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DepotRequest;
use App\Models\DocumentAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDepotRequestController extends Controller
{
    /**
     * LISTER toutes les demandes en attente (status = pending)
     * Route : GET /api/admin/depot-requests
     * Utilisé par PendingRequestsView.vue
     */
    public function index()
    {
        $requests = DepotRequest::with([
                'user:id,name,email',           // demandeur
                'reference.category:id,name',    // catégorie du document
                'reference.type:id,name',        // type du document
                'assignment.assignedTo:id,name', // gestionnaire assigné si déjà assigné
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($requests);
    }

    /**
     * LISTER toutes les assignations existantes
     * Route : GET /api/admin/assignments
     * Utilisé par AssignmentView.vue
     */
    public function assignments()
    {
        $assignments = DocumentAssignment::with([
                'depotRequest.reference:id,title,author',
                'depotRequest.user:id,name,email',
                'assignedBy:id,name',
                'assignedTo:id,name',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($assignments);
    }

    /**
     * ASSIGNER une demande à un gestionnaire
     * Route : POST /api/admin/assignments
     */
    public function assign(Request $request)
    {
        $validated = $request->validate([
            'depot_request_id' => 'required|exists:depot_requests,id',
            'assigned_to'      => 'required|exists:users,id',
            'instructions'     => 'nullable|string|max:1000',
        ]);

        // Vérifier que la demande est bien en statut 'pending'
        $depotRequest = DepotRequest::findOrFail($validated['depot_request_id']);

        if ($depotRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Cette demande a déjà été assignée ou traitée.',
            ], 422);
        }

        // Créer l'assignation
        $assignment = DocumentAssignment::create([
            'depot_request_id' => $validated['depot_request_id'],
            'assigned_by'      => Auth::id(),
            'assigned_to'      => $validated['assigned_to'],
            'instructions'     => $validated['instructions'] ?? null,
        ]);

        // Mettre à jour le statut de la demande
        $depotRequest->update(['status' => 'assigned']);

        return response()->json([
            'message'    => 'Demande assignée avec succès.',
            'assignment' => $assignment->load([
                'depotRequest.reference',
                'assignedTo:id,name',
                'assignedBy:id,name',
            ]),
        ], 201);
    }

    /**
     * LISTER les gestionnaires disponibles (pour le <select> du modal)
     * Route : GET /api/admin/gestionnaires
     */
    public function gestionnaires()
    {
        $gestionnaires = User::whereHas('role', fn($q) => $q->where('slug', 'gestionnaire'))
            ->where('is_active', true)
            ->get(['id', 'name', 'email']);

        return response()->json(['data' => $gestionnaires]);
    }
}