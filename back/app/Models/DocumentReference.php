<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentReference extends Model
{
    protected $fillable = [
        'title',
        'author',
        'publisher',
        'publication_year',
        'language',
        'isbn',
        'abstract',
        'category_id',
        'type_id',
        'submitted_by',
        'status',
        'cover_image',
    ];

    // ── Relations ──────────────────────────────────────────────────────

    // La référence appartient à une catégorie (Sciences, Droit...)
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // La référence appartient à un type (Thèse, Mémoire...)
    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    // L'utilisateur qui a soumis cette référence
    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    // Les fichiers physiques associés
    public function documents()
    {
        return $this->hasMany(Document::class, 'reference_id');
    }

    // La demande de dépôt liée
    public function depotRequest()
    {
        return $this->hasOne(DepotRequest::class, 'reference_id');
    }
    // Tous les "likes" reçus par cette référence
    public function likes()
    {
        return $this->hasMany(ReferenceLike::class, 'reference_id');
    }

    // L'historique des téléchargements de cette référence
    public function downloadLogs()
    {
        return $this->hasMany(DownloadLog::class, 'reference_id');
    }

    // L'historique des consultations (vues) de cette référence
    public function consultationLogs()
    {
        return $this->hasMany(ConsultationLog::class, 'reference_id');
    }

    /**
     * Vérifie si un utilisateur donné a déjà liké cette référence.
     * Utile pour afficher le bouton "like" plein ou vide côté front.
     */
    public function isLikedBy(?int $userId): bool
    {
        if (!$userId) {
            return false;
        }

        return $this->likes()->where('user_id', $userId)->exists();
    }
}