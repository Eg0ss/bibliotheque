<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentAssignment;
use App\Models\DepotRequest;
use App\Models\ValidationStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GestionnaireController extends Controller
{
    /**
     * LISTER les documents assignés au gestionnaire connecté
     * Route : GET /api/gestionnaire/documents
     * Filtre : seulement les demandes avec status = 'assigned'
     * (pas encore traitées par ce gestionnaire)
     */
    public function documents()
    {
        $assignments = DocumentAssignment::with([
                'depotRequest.reference.category',
                'depotRequest.reference.type',
                'depotRequest.reference.documents',
                'depotRequest.user:id,name,email',
                'assignedBy:id,name',
            ])
            ->where('assigned_to', Auth::id())
            // On ne montre que les demandes encore en statut 'assigned'
            ->whereHas('depotRequest', fn($q) => $q->where('status', 'assigned'))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($assignments);
    }

    /**
     * VOIR le détail d'une assignation
     * Route : GET /api/gestionnaire/documents/{assignmentId}
     */
    public function show(string $id)
    {
        $assignment = DocumentAssignment::with([
                'depotRequest.reference.category',
                'depotRequest.reference.type',
                'depotRequest.reference.documents',
                'depotRequest.user:id,name,email',
                'depotRequest.validationSteps.performer:id,name',
                'assignedBy:id,name',
            ])
            ->where('assigned_to', Auth::id())
            ->findOrFail($id);

        return response()->json(['data' => $assignment]);
    }

    /**
     * PRENDRE UNE DÉCISION sur un document assigné
     * Route : POST /api/gestionnaire/documents/{assignmentId}/decision
     *
     * body : { decision: 'manager_approved' | 'manager_rejected', comment: '...' }
     */
    public function decide(Request $request, string $id)
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:manager_approved,manager_rejected'],
            'comment'  => ['nullable', 'string', 'max:2000'],
        ]);

        // Vérifier que cette assignation appartient au gestionnaire connecté
        $assignment = DocumentAssignment::with('depotRequest')
            ->where('assigned_to', Auth::id())
            ->findOrFail($id);

        $depotRequest = $assignment->depotRequest;

        // Sécurité : on ne peut décider que sur une demande encore 'assigned'
        if ($depotRequest->status !== 'assigned') {
            return response()->json([
                'message' => 'Cette demande a déjà été traitée.',
            ], 422);
        }

        // Enregistrer la décision dans validation_steps
        ValidationStep::create([
            'depot_request_id' => $depotRequest->id,
            'performed_by'     => Auth::id(),
            'actor_role'       => 'gestionnaire',
            'decision'         => $validated['decision'],
            'comment'          => $validated['comment'] ?? null,
        ]);

        // Mettre à jour le statut de la demande
        $newStatus = $validated['decision'] === 'manager_approved'
            ? 'manager_approved'
            : 'rejected';

        $depotRequest->update(['status' => $newStatus]);

        $message = $validated['decision'] === 'manager_approved'
            ? 'Document validé et soumis à l\'administrateur.'
            : 'Document rejeté.';

        return response()->json(['message' => $message]);
    }

    /**
     * MES VALIDATIONS — documents déjà traités par ce gestionnaire
     * Route : GET /api/gestionnaire/validations
     */
    public function myValidations()
    {
        $steps = ValidationStep::with([
                'depotRequest.reference.category',
                'depotRequest.reference.type',
                'depotRequest.user:id,name,email',
            ])
            ->where('performed_by', Auth::id())
            ->where('actor_role', 'gestionnaire')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($steps);
    }
}