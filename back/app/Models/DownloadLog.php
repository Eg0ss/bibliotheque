<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DownloadLog extends Model
{
    protected $fillable = [
        'user_id',
        'reference_id',
    ];

    // L'utilisateur qui a téléchargé
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // La référence qui a été téléchargée
    public function reference()
    {
        return $this->belongsTo(DocumentReference::class, 'reference_id');
    }
}