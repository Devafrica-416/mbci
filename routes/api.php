<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BreakdownController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\NotificationController;

// Auth
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Utilisateur connecté
Route::middleware('auth:sanctum')->get('me', [AuthController::class, 'me']);

// Pannes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('breakdowns', [BreakdownController::class, 'index']);
    Route::post('breakdowns', [BreakdownController::class, 'store']);
    Route::get('breakdowns/{id}', [BreakdownController::class, 'show']);
    // Ajout d'une photo à une panne
    Route::post('breakdowns/{id}/photos', [BreakdownController::class, 'addPhoto']);

    // Véhicules
    Route::get('vehicles', [VehicleController::class, 'index']);
    Route::get('vehicles/{id}', [VehicleController::class, 'show']);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
}); 