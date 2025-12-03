<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\VotersController;
use App\Http\Controllers\CandidatesController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\auth\RegisteredUserController;
use App\Actions\Fortify\RegisterUser;
use App\Http\Controllers\ProfileController;


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
    
    // Election Routes
    Route::get('election',[ElectionController::class, 'election'])->name('admin.election');
    Route::post('election',[ElectionController::class, 'store'])->name('admin.election.store');
    Route::put('election/{election}',[ElectionController::class, 'update'])->name('admin.election.update');
    Route::delete('election/{election}',[ElectionController::class, 'destroy'])->name('admin.election.destroy');
    Route::post('election/{election}/activate',[ElectionController::class, 'activate'])->name('admin.election.activate');
    Route::post('election/{election}/deactivate',[ElectionController::class, 'deactivate'])->name('admin.election.deactivate');
    
    // Voters Routes (Read-only)
    Route::get('voters',[VotersController::class, 'index'])->name('admin.voters');
    
    // Candidates Routes
    Route::get('candidates',[CandidatesController::class, 'index'])->name('admin.candidates');
    Route::post('candidates',[CandidatesController::class, 'store'])->name('admin.candidates.store');
    Route::put('candidates/{candidate}',[CandidatesController::class, 'update'])->name('admin.candidates.update');
    Route::delete('candidates/{candidate}',[CandidatesController::class, 'destroy'])->name('admin.candidates.destroy');
    
    // Announcement Routes
    Route::get('announcement',[AnnouncementController::class, 'announcement'])->name('admin.announcement');
    
    Route::get('position',[PositionController::class, 'position'])->name('admin.position');
    Route::post('position',[PositionController::class, 'store'])->name('admin.position.store');
    Route::put('position/{position}',[PositionController::class, 'update'])->name('admin.position.update');
    Route::delete('position/{position}',[PositionController::class, 'destroy'])->name('admin.position.destroy');
});

// Voter Routes
Route::prefix('voter')->middleware(['auth', 'verified', 'voter'])->group(function(){
    Route::get('dashboard', function () {
        return Inertia::render('voter/Dashboard');
    })->name('voter.dashboard');

    Route::get('profile', [ProfileController::class, 'profile'])->name('voter.profile');
});

// Candidate Routes
Route::prefix('candidate')->middleware(['auth', 'verified', 'candidate'])->group(function(){
    Route::get('dashboard', function () {
        return Inertia::render('candidate/Dashboard');
    })->name('candidate.dashboard');
});

require __DIR__.'/settings.php';

// Test route for voter search
if (config('app.env') !== 'production') {
    require __DIR__.'/test.php';
}


