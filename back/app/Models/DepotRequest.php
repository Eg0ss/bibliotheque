<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepotRequest extends Model
{
    protected $fillable = [
        'user_id',
        'reference_id',
        'status',
        'rejection_reason',
    ];

    // L'utilisateur qui a fait la demande
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // La référence documentaire associée
    public function reference()
    {
        return $this->belongsTo(DocumentReference::class, 'reference_id');
    }
}