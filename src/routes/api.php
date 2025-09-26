<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExpenseTypeController;

Route::prefix('v1')->group(function () {
    Route::apiResource('expense-types', ExpenseTypeController::class);
});


