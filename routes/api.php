<?php

use App\Http\Controllers\JobController;
use App\Http\Controllers\FiltersController;
use Illuminate\Support\Facades\Route;

Route::get('/jobs', [JobController::class, 'index']);

Route::get('/filters/categories', [FiltersController::class, 'categories']);
Route::get('/filters/locations', [FiltersController::class, 'locations']);
Route::get('/filters/languages', [FiltersController::class, 'languages']);
Route::get('/filters/attributes', [FiltersController::class, 'attributes']);
