<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepotRequest\StoreDepotRequest;
use App\Models\DepotRequest;
use App\Models\Document;
use App\Models\DocumentReference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DepotRequestController extends Controller
{
    /**
     * LISTER les demandes de l'utilisateur connecté
     * Route : GET /api/user/depot-requests
     * Utilisé par MyRequestsView.vue
     */
    public function index()
    {
        $requests = DepotRequest::with(['reference.category', 'reference.type'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($requests);
    }

    /**
     * SOUMETTRE une nouvelle demande de dépôt
     * Route : POST /api/user/depot-requests
     *
     * Ce qu'on fait dans l'ordre :
     * 1. Enregistrer le fichier PDF sur le serveur
     * 2. Enregistrer l'image de couverture (si fournie)
     * 3. Créer la DocumentReference (notice bibliographique)
     * 4. Créer le Document (fichier physique lié)
     * 5. Créer la DepotRequest (demande de validation)
     */
   public function store(StoreDepotRequest $request)
{
    // ── 1. Stocker le fichier PDF (optionnel) ─────────────────────────
    // On vérifie d'abord si un fichier a été envoyé avec hasFile()
    // Sans cette vérification → PHP plante si file = null
    $filePath = null;
    if ($request->hasFile('file')) {
        $filePath = $request->file('file')->store('documents/private', 'local');
    }

    // ── 2. Stocker l'image de couverture (optionnelle) ────────────────
    $coverPath = null;
    if ($request->hasFile('cover_image')) {
        $coverPath = $request->file('cover_image')->store('covers', 'public');
    }

    // ── 3. Créer la référence documentaire ────────────────────────────
    $reference = DocumentReference::create([
        'title'            => $request->validated()['title'],
        'author'           => $request->validated()['author'],
        'publisher'        => $request->validated()['publisher'] ?? null,
        'publication_year' => $request->validated()['publication_year'] ?? null,
        'language'         => $request->validated()['language'] ?? 'fr',
        'isbn'             => $request->validated()['isbn'] ?? null,
        'abstract'         => $request->validated()['abstract'] ?? null,
        'category_id'      => $request->validated()['category_id'],
        'type_id'          => $request->validated()['type_id'],
        'submitted_by'     => Auth::id(),
        'status'           => 'pending',
        'cover_image'      => $coverPath,
    ]);

    // ── 4. Créer l'entrée Document SEULEMENT si un fichier a été fourni
    // On ne crée pas de ligne vide en base si aucun fichier n'a été uploadé
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

    // ── 5. Créer la demande de dépôt ──────────────────────────────────
    $depotRequest = DepotRequest::create([
        'user_id'      => Auth::id(),
        'reference_id' => $reference->id,
        'status'       => 'pending',
    ]);

    return response()->json([
        'message' => 'Votre demande a été soumise avec succès. Elle sera traitée prochainement.',
        'data'    => $depotRequest->load('reference'),
    ], 201);
}

    /**
     * VOIR une demande précise de l'utilisateur connecté
     * Route : GET /api/user/depot-requests/{id}
     */
    public function show(string $id)
    {
        // On s'assure que la demande appartient bien à l'utilisateur connecté
        $depotRequest = DepotRequest::with(['reference.category', 'reference.type'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json(['data' => $depotRequest]);
    }
}