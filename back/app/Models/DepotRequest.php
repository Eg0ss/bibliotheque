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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->belongsTo(DocumentReference::class, 'reference_id');
    }

    // Une demande peut avoir une assignation
    public function assignment()
    {
        return $this->hasOne(DocumentAssignment::class);
    }
}