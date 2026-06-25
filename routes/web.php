<?php

use App\Http\Controllers\MindGroupController;
use App\Http\Controllers\MindCalendarController;
use App\Http\Controllers\MindPersonController;
use App\Http\Controllers\ProfileController;
use App\Livewire\MindCalendar\Index as MindCalendarIndex;
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

    Route::get('/mind-social', [MindPersonController::class, 'index'])->name('mind-social.index');

    Route::post('/mind-people', [MindPersonController::class, 'store'])->name('mind-people.store');
    Route::put('/mind-people/{id}', [MindPersonController::class, 'update'])->name('mind-people.update');
    Route::delete('/mind-people/{id}', [MindPersonController::class, 'destroy'])->name('mind-people.destroy');
    Route::get('/mind-social/fisheye', [MindPersonController::class, 'fisheye'])->name('mind-social.fisheye');

    Route::post('/mind-groups', [MindGroupController::class, 'store'])->name('mind-groups.store');
    Route::put('/mind-groups/{id}', [MindGroupController::class, 'update'])->name('mind-groups.update');
    Route::delete('/mind-groups/{id}', [MindGroupController::class, 'destroy'])->name('mind-groups.destroy');

    Route::get('/mind-calendar', [App\Http\Controllers\MindCalendarController::class, 'index'])->name('mind-calendar.index');
});

require __DIR__.'/auth.php';
