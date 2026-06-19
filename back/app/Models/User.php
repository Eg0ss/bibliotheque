<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Sanctum donne à chaque user la capacité d'avoir des tokens

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Colonnes que l'on autorise à remplir en masse (sécurité)
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
    ];

    // Colonnes jamais renvoyées dans les réponses JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed', // Laravel hashera automatiquement le mot de passe
            'is_active'         => 'boolean',
        ];
    }

    // Relation : un User appartient à un Role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}