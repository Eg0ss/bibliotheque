<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Formate les données d'un utilisateur pour la réponse JSON
     * C'est ce que Vue.js reçoit : on contrôle exactement ce qui est exposé
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'is_active'  => $this->is_active,
            'created_at' => $this->created_at->format('d/m/Y'),

            // On inclut le rôle complet (id + name + slug) pour l'affichage
            'role' => $this->whenLoaded('role', [
                'id'   => $this->role?->id,
                'name' => $this->role?->name,
                'slug' => $this->role?->slug,
            ]),
        ];
    }
}