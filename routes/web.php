<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\ResultController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



Route::middleware(['auth', 'verified'])->group(function(){

    Route::get('/result',[ResultController::class, 'index'])->name('result.index');
    Route::get('/election',[ElectionController::class, 'index'])->name('election.index');
});

require __DIR__.'/settings.php';
