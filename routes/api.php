<?php

use App\Http\Controllers\AttributesController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\FiltersController;
use App\Http\Controllers\LanguagesController;
use App\Http\Controllers\LocationsController;
use Illuminate\Support\Facades\Route;

Route::get('/jobs', [JobController::class, 'index']);
Route::get('/jobs/{job}', [JobController::class, 'show']);
Route::post('/jobs', [JobController::class, 'store']);
Route::put('/jobs/{job}', [JobController::class, 'update']);
Route::delete('/jobs/{job}', [JobController::class, 'destroy']);

Route::get('/categories', [CategoriesController::class, 'index']);
Route::get('/categories/{category}', [CategoriesController::class, 'show']);
Route::get('/categories/{category}/jobs', [CategoriesController::class, 'jobs']);

Route::get('/languages', [LanguagesController::class, 'index']);
Route::get('/languages/{language}', [LanguagesController::class, 'show']);
Route::get('/languages/{language}/jobs', [LanguagesController::class, 'jobs']);

Route::get('/locations', [LocationsController::class, 'index']);
Route::get('/locations/{location}', [LocationsController::class, 'show']);
Route::get('/locations/{location}/jobs', [LocationsController::class, 'jobs']);

Route::get('/attributes', [AttributesController::class, 'index']);
Route::get('/attributes/{attribute}', [AttributesController::class, 'show']);
Route::get('/attributes/{attribute}/jobs', [AttributesController::class, 'jobs']);