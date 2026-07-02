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
 * DEMANDES EN ATTENTE D'ASSIGNATION
 * Route : GET /api/admin/depot-requests
 *
 * - Filtre strict : status = 'pending' UNIQUEMENT
 *   (les demandes assignées n'apparaissent plus ici)
 * - Recherche : titre, auteur, nom du demandeur
 * - Filtres : type_id, category_id
 */
public function index(Request $request)
{
    $query = DepotRequest::with([
            'user:id,name,email',
            'reference.category:id,name',
            'reference.type:id,name',
        ])
        // UNIQUEMENT les demandes pas encore assignées
        // Les autres (assigned, manager_approved, published, rejected)
        // sont gérées dans leurs propres rubriques
        ->where('status', 'pending')
        ->orderBy('created_at', 'desc');

    // ── Recherche texte ────────────────────────────────────────────────
    // Cherche dans le titre OU l'auteur de la référence OU le nom du demandeur
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            // Recherche dans les champs de la référence liée
            $q->whereHas('reference', function ($r) use ($search) {
                $r->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('author', 'LIKE', "%{$search}%");
            })
            // OU dans le nom du demandeur
            ->orWhereHas('user', function ($u) use ($search) {
                $u->where('name', 'LIKE', "%{$search}%");
            });
        });
    }

    // ── Filtre par type de document ────────────────────────────────────
    if ($request->filled('type_id')) {
        $query->whereHas('reference', fn($r) =>
            $r->where('type_id', $request->type_id)
        );
    }

    // ── Filtre par catégorie ────────────────────────────────────────────
    if ($request->filled('category_id')) {
        $query->whereHas('reference', fn($r) =>
            $r->where('category_id', $request->category_id)
        );
    }

    $requests = $query->paginate(15)->appends($request->query());

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
 * LISTER les gestionnaires avec leur charge de travail actuelle
 * Route : GET /api/admin/gestionnaires
 *
 * Pour chaque gestionnaire, on compte les assignations NON TRAITÉES
 * (status = 'assigned'), puis on trie par ordre croissant de charge
 * → le gestionnaire le moins chargé apparaît en premier dans le select
 */
public function gestionnaires()
{
    $gestionnaires = User::whereHas('role', fn($q) => $q->where('slug', 'gestionnaire'))
        ->where('is_active', true)
        // withCount crée automatiquement un attribut 'pending_assignments_count'
        // on filtre sur les demandes encore en status 'assigned' (non traitées)
        ->withCount([
            'assignments as pending_assignments_count' => function ($q) {
                $q->whereHas('depotRequest', fn($r) => $r->where('status', 'assigned'));
            }
        ])
        // Tri croissant : le moins chargé en premier
        ->orderBy('pending_assignments_count', 'asc')
        ->orderBy('name', 'asc') // à charge égale, on trie par nom
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

    // IMPORTANT : on ajoute 'reference' au with() pour pouvoir accéder à $depotRequest->reference
    $depotRequest = DepotRequest::with(['assignment', 'reference'])->findOrFail($id);

    // Enregistrer la décision admin dans validation_steps
    ValidationStep::create([
        'depot_request_id' => $depotRequest->id,
        'performed_by'     => Auth::id(),
        'actor_role'       => 'admin',
        'decision'         => $validated['decision'],
        'comment'          => $validated['comment'] ?? null,
    ]);

    // Correspondance décision → statut pour depot_requests
    $statusMap = [
        'published'      => 'published',
        'admin_rejected' => 'rejected',
        'resubmitted'    => 'assigned',
    ];

    // 1. Mise à jour de la DEMANDE (depot_requests)
    $depotRequest->update(['status' => $statusMap[$validated['decision']]]);

    // 2. Mise à jour de la RÉFÉRENCE (document_references)
    //    Le catalogue /catalogue lit WHERE status = 'published' sur cette table.
    //    Sans cette mise à jour, le catalogue reste vide même après publication.
    if ($depotRequest->reference) {
        $referenceStatusMap = [
            'published'      => 'published',
            'admin_rejected' => 'rejected',
            'resubmitted'    => 'assigned',
        ];

        $depotRequest->reference->update([
            'status' => $referenceStatusMap[$validated['decision']]
        ]);
    }

    $messages = [
        'published'      => 'Référence publiée avec succès.',
        'admin_rejected' => 'Référence rejetée définitivement.',
        'resubmitted'    => 'Référence renvoyée au gestionnaire pour re-vérification.',
    ];

    return response()->json(['message' => $messages[$validated['decision']]]);
}

/**
 * STATISTIQUES du tableau de bord admin
 * Route : GET /api/admin/stats
 *
 * Retourne :
 * - Utilisateurs actifs / inactifs (hors admins)
 * - Références publiées / rejetées par l'admin connecté
 * - Demandes en attente d'assignation (status = pending)
 * - Total des demandes traitées par tous les gestionnaires
 */
public function stats()
{
    // ── 1. Utilisateurs hors admin ─────────────────────────────────────
    // On exclut le rôle admin du décompte
    $usersBase = User::whereHas('role', fn($q) =>
        $q->where('slug', '!=', 'admin')
    );

    $usersActifs   = (clone $usersBase)->where('is_active', true)->count();
    $usersInactifs = (clone $usersBase)->where('is_active', false)->count();

    // ── 2. Références publiées / rejetées par l'admin connecté ────────
    // On cherche dans validation_steps les décisions de l'admin courant
    $refPubliees = \App\Models\ValidationStep::where('performed_by', Auth::id())
        ->where('actor_role', 'admin')
        ->where('decision', 'published')
        ->count();

    $refRejetees = \App\Models\ValidationStep::where('performed_by', Auth::id())
        ->where('actor_role', 'admin')
        ->where('decision', 'admin_rejected')
        ->count();

    // ── 3. Demandes en attente d'assignation ──────────────────────────
    // status = 'pending' = soumises mais pas encore confiées à un gestionnaire
    $enAttente = DepotRequest::where('status', 'pending')->count();

    // ── 4. Total des demandes traitées par tous les gestionnaires ─────
    // Une demande est "traitée par un gestionnaire" quand une validation_step
    // de rôle 'gestionnaire' a été créée (acceptée ou rejetée)
    $traitesParGestionnaires = \App\Models\ValidationStep::where('actor_role', 'gestionnaire')
        ->count();

    return response()->json([
        'data' => [
            'utilisateurs' => [
                'actifs'   => $usersActifs,
                'inactifs' => $usersInactifs,
                'total'    => $usersActifs + $usersInactifs,
            ],
            'references' => [
                'publiees' => $refPubliees,
                'rejetees' => $refRejetees,
            ],
            'demandes' => [
                'en_attente'               => $enAttente,
                'traitees_gestionnaires'   => $traitesParGestionnaires,
            ],
        ]
    ]);
}
}
