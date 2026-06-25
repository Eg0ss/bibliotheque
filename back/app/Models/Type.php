<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Type;

class TypeController extends Controller
{
    /**
     * Retourner tous les types pour alimenter le <select> du formulaire
     * Route : GET /api/user/types
     */
    public function index()
    {
        $types = Type::orderBy('name')->get(['id', 'name', 'slug']);

        return response()->json(['data' => $types]);
    }
}