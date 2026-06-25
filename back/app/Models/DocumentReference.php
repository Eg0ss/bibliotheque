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
}