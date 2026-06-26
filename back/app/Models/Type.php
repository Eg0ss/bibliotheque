<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function references()
    {
        return $this->hasMany(DocumentReference::class);
    }
}