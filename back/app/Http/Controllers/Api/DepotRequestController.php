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
     * LISTER les demandes de l'utilisateur connecté
     * GET /api/user/depot-requests
     *
     * authorize('viewAny', DepotRequest::class)
     * → DepotRequestPolicy::viewAny($userConnecte)
     * → true si le compte est actif
     */
    public function index()
    {
        $this->authorize('viewAny', DepotRequest::class);

        // Même si viewAny autorise l'accès général, on filtre quand même
        // sur user_id pour que chacun ne voie QUE ses propres demandes
        $requests = DepotRequest::with(['reference.category', 'reference.type'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

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
}