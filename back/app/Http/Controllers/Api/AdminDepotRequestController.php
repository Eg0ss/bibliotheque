<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DepotRequest;
use App\Models\DocumentAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ValidationStep;

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

    /**
 * DEMANDES TRAITÉES — références validées ou rejetées par les gestionnaires
 * Route : GET /api/admin/depot-requests/traites
 * Affiche les demandes avec status = 'manager_approved' ou 'rejected'
 */
public function traites()
{
    $requests = DepotRequest::with([
            'user:id,name,email',
            'reference.category:id,name',
            'reference.type:id,name',
            'assignment.assignedTo:id,name',
            'latestValidationStep.performer:id,name',
        ])
        ->whereIn('status', ['manager_approved', 'rejected'])
        ->orderBy('updated_at', 'desc')
        ->paginate(15);

    return response()->json($requests);
}

/**
 * DÉCISION FINALE de l'admin sur une demande traitée par le gestionnaire
 * Route : POST /api/admin/depot-requests/{id}/decision
 *
 * body : { decision: 'published' | 'admin_rejected' | 'resubmitted', comment: '...' }
 */
public function finalDecision(Request $request, string $id)
{
    $validated = $request->validate([
        'decision' => ['required', 'in:published,admin_rejected,resubmitted'],
        'comment'  => ['nullable', 'string', 'max:2000'],
    ]);

    $depotRequest = DepotRequest::with('assignment')->findOrFail($id);

    // Enregistrer la décision admin dans validation_steps
    ValidationStep::create([
        'depot_request_id' => $depotRequest->id,
        'performed_by'     => Auth::id(),
        'actor_role'       => 'admin',
        'decision'         => $validated['decision'],
        'comment'          => $validated['comment'] ?? null,
    ]);

    // Mettre à jour le statut selon la décision
    $statusMap = [
        'published'      => 'published',
        'admin_rejected' => 'rejected',
        'resubmitted'    => 'assigned',  // repart au gestionnaire
    ];

    $depotRequest->update(['status' => $statusMap[$validated['decision']]]);

    $messages = [
        'published'      => 'Référence publiée avec succès.',
        'admin_rejected' => 'Référence rejetée définitivement.',
        'resubmitted'    => 'Référence renvoyée au gestionnaire pour re-vérification.',
    ];

    return response()->json(['message' => $messages[$validated['decision']]]);
}
}