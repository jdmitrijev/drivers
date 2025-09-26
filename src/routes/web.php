<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('reports/{trip}', [ReportController::class, 'show'])->name('reports.show');
