<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceLike extends Model
{
    protected $fillable = [
        'user_id',
        'reference_id',
    ];

    // Le like appartient à un utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Le like appartient à une référence documentaire
    public function reference()
    {
        return $this->belongsTo(DocumentReference::class, 'reference_id');
    }
}