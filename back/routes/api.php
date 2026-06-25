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

        Route::apiResource('users', UserController::class);
        Route::patch('users/{id}/toggle-status', [UserController::class, 'toggleStatus']);
        Route::get('roles', [UserController::class, 'getRoles']);

        // Catégories admin (avec pagination)
        Route::get('categories/all', [CategoryController::class, 'all']);
        Route::apiResource('categories', CategoryController::class);
    });

    /*
|--------------------------------------------------------------------------
| Routes Utilisateur connecté
|--------------------------------------------------------------------------
*/
    Route::prefix('user')->group(function () {
        
        Route::get('categories', [CategoryController::class, 'all']);
        Route::get('types',      [TypeController::class, 'index']);

        // Demandes de dépôt
        Route::get('depot-requests',      [DepotRequestController::class, 'index']);
        Route::post('depot-requests',     [DepotRequestController::class, 'store']);
        Route::get('depot-requests/{id}', [DepotRequestController::class, 'show']);
    });
});
