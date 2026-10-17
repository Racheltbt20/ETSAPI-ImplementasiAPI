<?php

use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

Route::post('/tracks/search', [TrackController::class, 'search']);
Route::apiResource('tracks', TrackController::class);