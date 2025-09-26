<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\TeamController;

Route::prefix('v1')->group(function () {
    Route::apiResource('expense-types', ExpenseTypeController::class);
    Route::apiResource('teams', TeamController::class);
    Route::post('teams/{team}/drivers', [TeamController::class, 'addDriver']);
    Route::delete('teams/{team}/drivers', [TeamController::class, 'removeDriver']);
});


