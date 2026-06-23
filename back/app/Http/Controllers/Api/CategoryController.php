<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Liste toutes les catégories
     * GET /api/admin/categories
     */
    public function index()
    {
        $categories = Category::with('parent')
            ->orderBy('name')
            ->paginate(15);

        return CategoryResource::collection($categories);
    }

    /**
     * Créer une catégorie
     * POST /api/admin/categories
     */
    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());
        $category->load('parent');

        return response()->json([
            'message'  => 'Catégorie créée avec succès.',
            'category' => new CategoryResource($category),
        ], 201);
    }

    /**
     * Voir une catégorie
     * GET /api/admin/categories/{id}
     */
    public function show(string $id)
    {
        $category = Category::with('parent')->findOrFail($id);
        return new CategoryResource($category);
    }

    /**
     * Toutes les catégories sans pagination (pour les selects)
     * GET /api/admin/categories/all
     */
    public function all()
    {
        $categories = Category::orderBy('name')->get(['id', 'name', 'parent_id']);
        return response()->json(['data' => $categories]);
    }

    public function update(Request $request, string $id)
    {
        // À implémenter
    }

    public function destroy(string $id)
    {
        // À implémenter
    }
}