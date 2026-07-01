<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepotRequest\StoreDepotRequest;
use App\Models\DepotRequest;
use App\Models\Document;
use App\Models\DocumentReference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepotRequestController extends Controller
{
   /**
 * LISTER les demandes avec recherche et filtre par statut
 * GET /api/user/depot-requests?search=social&status=published&page=1
 */
public function index(Request $request)
{
    $this->authorize('viewAny', DepotRequest::class);

    $query = DepotRequest::with(['reference.category', 'reference.type'])
        ->where('user_id', Auth::id())
        ->orderBy('created_at', 'desc');

    // Recherche par titre ou auteur de la référence liée
    if ($request->filled('search')) {
        $search = $request->search;
        $query->whereHas('reference', function ($q) use ($search) {
            $q->where('title', 'LIKE', "%{$search}%")
              ->orWhere('author', 'LIKE', "%{$search}%");
        });
    }

    // Filtre par statut
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $requests = $query->paginate(10)->appends($request->query());

    return response()->json($requests);
}

    /**
     * SOUMETTRE une nouvelle demande de dépôt
     * POST /api/user/depot-requests
     *
     * authorize('create', DepotRequest::class)
     * → DepotRequestPolicy::create($userConnecte)
     * → bloque les comptes désactivés
     */
    public function store(StoreDepotRequest $request)
{
    $this->authorize('create', DepotRequest::class);

    // ── 1. Stocker le fichier PDF (optionnel) ──────────────────────────
    $filePath = null;
    if ($request->hasFile('file') && $request->file('file')->isValid()) {
        // isValid() vérifie qu'il n'y a pas eu d'erreur pendant l'upload
        $filePath = $request->file('file')->store('documents/private', 'local');
    }

    // ── 2. Stocker l'image de couverture (optionnelle) ─────────────────
    $coverPath = null;
    if ($request->hasFile('cover_image') && $request->file('cover_image')->isValid()) {
        $coverPath = $request->file('cover_image')->store('covers', 'public');
    }

    // ── 3. Créer la référence documentaire ─────────────────────────────
    $reference = DocumentReference::create([
        'title'            => $request->validated()['title'],
        'author'           => $request->validated()['author'],
        'publisher'        => $request->validated()['publisher'] ?? null,
        'publication_year' => $request->validated()['publication_year'] ?? null,
        'language'         => $request->validated()['language'] ?? 'fr',
        // On convertit la chaîne vide en null pour éviter le conflit unique
        'isbn'             => !empty($request->validated()['isbn'])
                                ? $request->validated()['isbn']
                                : null,
        'abstract'         => $request->validated()['abstract'] ?? null,
        'category_id'      => $request->validated()['category_id'],
        'type_id'          => $request->validated()['type_id'],
        'submitted_by'     => Auth::id(),
        'status'           => 'pending',
        'cover_image'      => $coverPath,
    ]);

    // ── 4. Créer l'entrée Document si fichier fourni ────────────────────
    if ($filePath) {
        Document::create([
            'reference_id'  => $reference->id,
            'file_path'     => $filePath,
            'original_name' => $request->file('file')->getClientOriginalName(),
            'mime_type'     => $request->file('file')->getMimeType(),
            'file_size'     => $request->file('file')->getSize(),
            'version'       => 1,
        ]);
    }

    // ── 5. Créer la demande de dépôt ────────────────────────────────────
    $depotRequest = DepotRequest::create([
        'user_id'      => Auth::id(),
        'reference_id' => $reference->id,
        'status'       => 'pending',
    ]);

    return response()->json([
        'message' => 'Votre demande a été soumise avec succès.',
        'data'    => $depotRequest->load('reference'),
    ], 201);
}

    /**
     * VOIR une demande précise
     * GET /api/user/depot-requests/{id}
     *
     * authorize('view', $depotRequest)
     * → DepotRequestPolicy::view($userConnecte, $depotRequest)
     * → true si propriétaire OU gestionnaire assigné
     *
     * Différence avec avant : on ne filtre plus par ->where('user_id', ...)
     * dans la requête — c'est maintenant la Policy qui décide, ce qui
     * permet aussi au gestionnaire assigné de consulter la demande.
     */
    public function show(string $id)
    {
        $depotRequest = DepotRequest::with(['reference.category', 'reference.type', 'assignment'])
            ->findOrFail($id);

        $this->authorize('view', $depotRequest);

        return response()->json(['data' => $depotRequest]);
    }

    /**
 * STATISTIQUES personnelles de l'utilisateur connecté
 * GET /api/user/stats
 *
 * Retourne les compteurs par statut pour le tableau de bord
 */
public function stats()
{
    $userId = Auth::id();

    // Une seule requête SQL avec GROUP BY — plus performant
    // que 4 requêtes COUNT séparées
    $rows = DepotRequest::where('user_id', $userId)
        ->selectRaw('status, COUNT(*) as total')
        ->groupBy('status')
        ->pluck('total', 'status');

    // On structure la réponse avec des valeurs par défaut à 0
    // si un statut n'a aucune entrée (absent du GROUP BY)
    return response()->json([
        'data' => [
            'total'            => $rows->sum(),
            'pending'          => $rows->get('pending', 0),
            'assigned'         => $rows->get('assigned', 0),
            'manager_approved' => $rows->get('manager_approved', 0),
            'published'        => $rows->get('published', 0),
            'rejected'         => $rows->get('rejected', 0),
            // En attente = tout ce qui n'est pas encore publié ni rejeté
            'in_progress'      => $rows->filter(
                fn($v, $k) => !in_array($k, ['published', 'rejected'])
            )->sum(),
        ]
    ]);
}
}