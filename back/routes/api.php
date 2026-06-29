<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TypeController;
use App\Http\Controllers\Api\DepotRequestController;
use App\Http\Controllers\Api\AdminDepotRequestController;
use App\Http\Controllers\Api\GestionnaireController;

/*
|--------------------------------------------------------------------------
| Routes publiques (sans authentification)
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Types et catégories accessibles sans connexion
Route::get('/types',      [TypeController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'all']);

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
    | Routes Admin
    |----------------------------------------------------------------------
    */
    Route::prefix('admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::patch('users/{id}/toggle-status', [UserController::class, 'toggleStatus']);
        Route::get('roles', [UserController::class, 'getRoles']);

        // Catégories & Types 
        Route::get('categories/all', [CategoryController::class, 'all']);
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('types', TypeController::class);

        // ── Demandes en attente ──────────────────────────────────────
        Route::get('depot-requests',   [AdminDepotRequestController::class, 'index']);

        // ── Assignations ─────────────────────────────────────────────
        Route::get('assignments',      [AdminDepotRequestController::class, 'assignments']);
        Route::post('assignments',     [AdminDepotRequestController::class, 'assign']);
        Route::get('gestionnaires',    [AdminDepotRequestController::class, 'gestionnaires']);
    });

    /*
    |----------------------------------------------------------------------
    | Routes Utilisateur connecté
    |----------------------------------------------------------------------
    */
    Route::prefix('user')->group(function () {
        // Demandes de dépôt
        Route::get('depot-requests',      [DepotRequestController::class, 'index']);
        Route::post('depot-requests',     [DepotRequestController::class, 'store']);
        Route::get('depot-requests/{id}', [DepotRequestController::class, 'show']);
    });

    /*
|----------------------------------------------------------------------
| Routes Gestionnaire
|----------------------------------------------------------------------
*/
    Route::prefix('gestionnaire')->group(function () {
        // Documents assignés au gestionnaire connecté
        Route::get('documents', [GestionnaireController::class, 'documents']);
    });
});
