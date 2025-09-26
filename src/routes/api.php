<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('expense-types', 'App\\Http\\Controllers\\ExpenseTypeController');
    Route::apiResource('teams', 'App\\Http\\Controllers\\TeamController');
    Route::post('teams/{team}/drivers', ['App\\Http\\Controllers\\TeamController', 'addDriver']);
    Route::delete('teams/{team}/drivers', ['App\\Http\\Controllers\\TeamController', 'removeDriver']);

    Route::apiResource('trips', 'App\\Http\\Controllers\\TripController');
    Route::post('trips/{trip}/expenses', ['App\\Http\\Controllers\\TripExpenseController', 'store']);
    Route::put('trips/{trip}/expenses/{expense}', ['App\\Http\\Controllers\\TripExpenseController', 'update']);
    Route::delete('trips/{trip}/expenses/{expense}', ['App\\Http\\Controllers\\TripExpenseController', 'destroy']);
});


