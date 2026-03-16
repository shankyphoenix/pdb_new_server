<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UtilitiesController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('navigation', [UtilitiesController::class,'navigation'])->middleware(['auth:sanctum']);
Route::get('navigation/logo', [UtilitiesController::class,'navigation_logo'])->middleware(['auth:sanctum']);
Route::get('system_config', [UtilitiesController::class,'system_config'])->middleware(['auth:sanctum']);
Route::get('homepage/status', [UtilitiesController::class,'homepage'])->middleware(['auth:sanctum']);


Route::middleware(['auth:sanctum'])->prefix('pos')->namespace('\App\Http\Controllers\Pos')->group(function () {
    Route::get('filters', 'FilterController@filters');    
    Route::get('list', 'ServiceController@list');    
});
