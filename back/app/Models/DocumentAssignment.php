<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentAssignment extends Model
{
    protected $fillable = [
        'depot_request_id',
        'assigned_by',
        'assigned_at',
        'due_date',
        'assigned_to',
        'instructions',
    ];

   
protected $casts = [

    // Date + heure
    'assigned_at' => 'datetime',

    // Date uniquement
    'due_date' => 'date',

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