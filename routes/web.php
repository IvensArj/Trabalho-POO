<?php

use App\Http\Controllers\MindGroupController;
use App\Http\Controllers\MindPersonController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/mind-social', [MindPersonController::class, 'index'])
    ->name('mind-social.index');
    Route::resource('mind-people', MindPersonController::class);
    Route::resource('mind-groups', MindGroupController::class);
});

require __DIR__.'/auth.php';
