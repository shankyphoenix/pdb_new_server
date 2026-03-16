<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BatchStatusController;
use App\Models\User;
use App\Http\Controllers\TestController;


Route::get('/hello', function () {
    dump(User::first()->name);
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware('auth')->group(function () {
    Route::get('/batch-status', [BatchStatusController::class, 'index']);
    Route::post('/batch-status', [BatchStatusController::class, 'store']);
});

Route::get('/authorize_ip', [TestController::class, 'authorize_ip']);



require __DIR__.'/auth.php';
