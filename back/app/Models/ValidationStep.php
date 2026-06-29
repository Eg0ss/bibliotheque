<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidationStep extends Model
{
    protected $fillable = [
        'depot_request_id',
        'performed_by',
        'actor_role',
        'decision',
        'comment',
    ];

    // La demande concernée
    public function depotRequest()
    {
        return $this->belongsTo(DepotRequest::class);
    }

    // L'acteur qui a pris la décision
    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}