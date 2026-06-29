<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentAssignment extends Model
{
    protected $fillable = [
        'depot_request_id',
        'assigned_by',
        'assigned_to',
        'instructions',
    ];

    // La demande concernée
    public function depotRequest()
    {
        return $this->belongsTo(DepotRequest::class);
    }

    // L'admin qui a assigné
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Le gestionnaire assigné
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}