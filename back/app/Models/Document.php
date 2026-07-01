<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'reference_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'version',
    ];

    public function reference()
    {
        return $this->belongsTo(DocumentReference::class, 'reference_id');
    }
}