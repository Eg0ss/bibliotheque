<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TypeController extends Controller
{
    /**
     * Retourner tous les types pour les selects du formulaire
     * GET /api/types
     */
    public function index()
    {
        $types = Type::orderBy('name')->get(['id', 'name', 'slug']);

        return response()->json(['data' => $types]);
    }

    /**
     * Liste paginée pour l'admin
     * GET /api/admin/types
     */
    public function adminIndex()
    {
        $types = Type::orderBy('name')->paginate(15);

        return response()->json([
            'data' => $types->items(),
            'meta' => [
                'current_page' => $types->currentPage(),
                'last_page'    => $types->lastPage(),
                'total'        => $types->total(),
            ],
        ]);
    }

    /**
     * Créer un type
     * POST /api/admin/types
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:types,name',
            'description' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'name.unique'   => 'Ce nom de type existe déjà.',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $type = Type::create($validated);

        return response()->json([
            'message' => 'Type créé avec succès.',
            'type'    => $type,
        ], 201);
    }
}