<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\VotersController;
use App\Http\Controllers\CandidatesController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\auth\RegisteredUserController;
use App\Actions\Fortify\RegisterUser;


Route::view('/', 'index')->name('home');

Route::view('/index', 'index')->name('index');
Route::view('/about', 'about')->name('about');
Route::view('/contact', 'contact')->name('contact');


Route::get('dashboard', function () {
    return Inertia::render('admin/Dashboard', []);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/register', [RegisteredUserController::class, 'store']);

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'verified', 'admin'])->group(function(){
    Route::get('dashboard', function () {
        return Inertia::render('admin/Dashboard');
    })->name('admin.dashboard');

    Route::get('result',[ResultController::class, 'result'])->name('admin.result');
    Route::get('election',[ElectionController::class, 'election'])->name('admin.election');
    Route::get('voters',[VotersController::class, 'voters'])->name('admin.voters');
    Route::get('candidates',[CandidatesController::class, 'candidates'])->name('admin.candidates');
    Route::get('announcement',[AnnouncementController::class, 'announcement'])->name('admin.announcement');
});

// Voter Routes
Route::prefix('voter')->middleware(['auth', 'verified', 'voter'])->group(function(){
    Route::get('dashboard', function () {
        return Inertia::render('voter/Dashboard');
    })->name('voter.dashboard');
});

// Candidate Routes
Route::prefix('candidate')->middleware(['auth', 'verified', 'candidate'])->group(function(){
    Route::get('dashboard', function () {
        return Inertia::render('candidate/Dashboard');
    })->name('candidate.dashboard');
});

require __DIR__.'/settings.php';


