<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TypeController;
use App\Http\Controllers\Api\DepotRequestController;

/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Routes protégées (session Sanctum obligatoire)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    /*
    |----------------------------------------------------------------------
    | Routes Admin - Gestion des utilisateurs
    | Préfixe /api/admin/users
    |----------------------------------------------------------------------
    */
    Route::prefix('admin')->group(function () {

        // GET    /api/admin/users         → liste paginée
        // POST   /api/admin/users         → créer un user
        // GET    /api/admin/users/{id}    → voir un user
        // PUT    /api/admin/users/{id}    → modifier un user
        // DELETE /api/admin/users/{id}    → supprimer
        Route::apiResource('users', UserController::class);
        Route::patch('users/{id}/toggle-status', [UserController::class, 'toggleStatus']);
        Route::get('roles', [UserController::class, 'getRoles']);

        // Catégories
        Route::get('categories/all', [CategoryController::class, 'all']); // avant apiResource
        Route::apiResource('categories', CategoryController::class);
    });

    /*
|--------------------------------------------------------------------------
| Routes Utilisateur connecté
|--------------------------------------------------------------------------
*/
Route::prefix('user')->group(function () {

    // GET  /api/user/depot-requests      → liste des demandes du user
    // POST /api/user/depot-requests      → soumettre une nouvelle demande
    // GET  /api/user/depot-requests/{id} → voir une demande précise
    Route::get('depot-requests',       [DepotRequestController::class, 'index']);
    Route::post('depot-requests',      [DepotRequestController::class, 'store']);
    Route::get('depot-requests/{id}',  [DepotRequestController::class, 'show']);

    // Données nécessaires pour remplir les selects du formulaire
    // (accessibles à tout utilisateur connecté)
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('types',      [TypeController::class, 'index']);
});

    
});
