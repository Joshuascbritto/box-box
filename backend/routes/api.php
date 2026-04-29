<?php

use App\Http\Controllers\AdminRaceController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\RaceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — box-box
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'time' => now()->toIso8601String(),
    ]);
});

// --- Auth ------------------------------------------------------------------
Route::post('/auth/request-link', [AuthController::class, 'requestLink']);
Route::post('/auth/verify',       [AuthController::class, 'verify']);

// --- Public ----------------------------------------------------------------
Route::get('/races',           [RaceController::class, 'index']);
Route::get('/races/{race}',    [RaceController::class, 'show']);
Route::get('/drivers',         [DriverController::class, 'index']);
Route::get('/leaderboard',     [LeaderboardController::class, 'index']);
Route::get('/archive',         [ArchiveController::class, 'index']);

// --- Authenticated ---------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    Route::post('/predictions',                  [PredictionController::class, 'store']);
    Route::get('/predictions/me',                [PredictionController::class, 'mine']);
    Route::get('/predictions/race/{race}',       [PredictionController::class, 'forRace']);

    // --- Admin -------------------------------------------------------------
    Route::middleware('admin')->group(function () {
        Route::post('/admin/races/{race}/result', [AdminRaceController::class, 'recordResult']);
    });
});
