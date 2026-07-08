<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentReference;
use App\Models\DownloadLog;
use App\Models\ReferenceLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReferenceInteractionController extends Controller
{
    /**
     * Vérifie que le compte de l'utilisateur connecté est autorisé
     * à télécharger / liker (compte actif ET non suspendu).
     *
     * Retourne une réponse JSON d'erreur si bloqué, ou null si tout va bien.
     * Le code 'account_suspended' / 'account_disabled' permet au front
     * d'afficher le bon message dans la notification PrimeVue.
     */
    private function checkAccountStatus(Request $request)
    {
        $user = $request->user();

        if ($user->is_suspended) {
            return response()->json([
                'code'    => 'account_suspended',
                'message' => "Votre compte a été suspendu. Veuillez contacter l'administrateur.",
            ], 403);
        }

        if (!$user->is_active) {
            return response()->json([
                'code'    => 'account_disabled',
                'message' => "Votre compte est désactivé. Veuillez contacter l'administrateur.",
            ], 403);
        }

        return null;
    }

    /**
     * TÉLÉCHARGEMENT d'un PDF associé à une référence.
     * URL : GET /api/references/{id}/telecharger
     * Nécessite : auth:sanctum (voir routes/api.php)
     *
     * Le compteur s'incrémente à CHAQUE téléchargement, même si c'est
     * toujours le même utilisateur qui télécharge plusieurs fois.
     */
    public function download(Request $request, string $id)
    {
        // 1. Compte actif et non suspendu ?
        $blocked = $this->checkAccountStatus($request);
        if ($blocked) {
            return $blocked;
        }

        // 2. La référence doit être publiée
        $reference = DocumentReference::where('status', 'published')
            ->findOrFail($id);

        // 3. Récupérer le fichier PDF le plus récent lié à cette référence
        $document = $reference->documents()->latest()->first();

        if (!$document) {
            return response()->json([
                'message' => "Aucun fichier n'est disponible pour cette référence.",
            ], 404);
        }

        if (!Storage::disk('local')->exists($document->file_path)) {
            return response()->json([
                'message' => "Le fichier est introuvable sur le serveur.",
            ], 404);
        }

        // 4. Historiser le téléchargement
        DownloadLog::create([
            'user_id'      => $request->user()->id,
            'reference_id' => $reference->id,
        ]);

        // 5. Incrémenter le compteur (à chaque fois, sans exception)
        $reference->increment('downloads_count');

        // 6. Envoyer le fichier au navigateur (déclenche le téléchargement)
        return Storage::disk('local')->download(
            $document->file_path,
            $document->original_name
        );
    }

    /**
     * LIKE / UNLIKE d'une référence (toggle).
     * URL : POST /api/references/{id}/like
     * Nécessite : auth:sanctum
     *
     * Premier clic  → like  (compteur +1)
     * Second clic    → unlike (compteur -1)
     */
    public function toggleLike(Request $request, string $id)
    {
        $blocked = $this->checkAccountStatus($request);
        if ($blocked) {
            return $blocked;
        }

        $reference = DocumentReference::where('status', 'published')
            ->findOrFail($id);

        $userId = $request->user()->id;

        // On cherche si l'utilisateur a déjà liké cette référence
        $existingLike = ReferenceLike::where('user_id', $userId)
            ->where('reference_id', $reference->id)
            ->first();

        if ($existingLike) {
            // Il avait déjà liké → on retire le like
            $existingLike->delete();
            $reference->decrement('likes_count');
            $liked = false;
        } else {
            // Premier like → on l'enregistre
            ReferenceLike::create([
                'user_id'      => $userId,
                'reference_id' => $reference->id,
            ]);
            $reference->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'liked'       => $liked,
            'likes_count' => $reference->fresh()->likes_count,
        ]);
    }
}