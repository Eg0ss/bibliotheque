<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'parent_id'];

    // Génère automatiquement le slug depuis le name avant sauvegarde
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug = Str::slug($category->name);
        });
    }

    // Une catégorie appartient à une catégorie parente (nullable)
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Une catégorie a plusieurs sous-catégories
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}