<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\VotersController;
use App\Http\Controllers\CandidatesController;
use App\Http\Controllers\AnnouncementController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::view('/index', 'index')->name('index');
Route::view('/about', 'about')->name('about');
Route::view('/contact', 'contact')->name('contact');


Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



Route::middleware(['auth', 'verified'])->group(function(){

    Route::get('/result',[ResultController::class, 'index'])->name('result.index');
    Route::get('/election',[ElectionController::class, 'index'])->name('election.index');
    Route::get('/voters',[VotersController::class, 'index'])->name('Voters.index');
    Route::get('/candidates',[CandidatesController::class, 'index'])->name('Candidates.index');
    Route::get('/announcement',[AnnouncementController::class, 'index'])->name('Announcement.index');
    
});

require __DIR__.'/settings.php';


