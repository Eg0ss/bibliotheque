<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TypeController;
use App\Http\Controllers\Api\DepotRequestController;
use App\Http\Controllers\Api\AdminDepotRequestController;
use App\Http\Controllers\Api\GestionnaireController;
use App\Http\Controllers\Api\ReferenceController;

// ── Routes publiques ─────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::get('/types',      [TypeController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'all']);
Route::get('/references',      [ReferenceController::class, 'index']); // Liste publiée
Route::get('/references/{id}', [ReferenceController::class, 'show']);  // Détail
// ── Routes protégées ─────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // ── ADMIN ────────────────────────────────────────────────────────────
    Route::prefix('admin')->group(function () {

        Route::apiResource('users', UserController::class);
        Route::patch('users/{id}/toggle-status', [UserController::class, 'toggleStatus']);
        Route::get('roles', [UserController::class, 'getRoles']);

        Route::get('categories/all', [CategoryController::class, 'all']);
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('types', TypeController::class);

        // Workflow
        Route::get('depot-requests',                      [AdminDepotRequestController::class, 'index']);
        Route::get('depot-requests/traites',              [AdminDepotRequestController::class, 'traites']);
        Route::post('depot-requests/{id}/decision',       [AdminDepotRequestController::class, 'finalDecision']);

        Route::get('assignments',   [AdminDepotRequestController::class, 'assignments']);
        Route::post('assignments',  [AdminDepotRequestController::class, 'assign']);
        Route::get('gestionnaires', [AdminDepotRequestController::class, 'gestionnaires']);
        Route::get('stats', [AdminDepotRequestController::class, 'stats']);
    });

    // ── UTILISATEUR ──────────────────────────────────────────────────────
    Route::prefix('user')->group(function () {
        Route::get('depot-requests',      [DepotRequestController::class, 'index']);
        Route::post('depot-requests',     [DepotRequestController::class, 'store']);
        Route::get('depot-requests/{id}', [DepotRequestController::class, 'show']);
        Route::get('stats', [DepotRequestController::class, 'stats']);
    });

    // ── GESTIONNAIRE ─────────────────────────────────────────────────────
    Route::prefix('gestionnaire')->group(function () {
        Route::get('documents',                          [GestionnaireController::class, 'documents']);
        Route::get('documents/{id}',                     [GestionnaireController::class, 'show']);
        Route::post('documents/{id}/decision',           [GestionnaireController::class, 'decide']);
        Route::get('validations',                        [GestionnaireController::class, 'myValidations']);
    });
});