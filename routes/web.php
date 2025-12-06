<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\VotersController;
use App\Http\Controllers\CandidatesController;
use App\Http\Controllers\AnnouncementsController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\auth\RegisteredUserController;
use App\Actions\Fortify\RegisterUser;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VotingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CandidateController;


Route::view('/', 'index')->name('home');

Route::view('/index', 'index')->name('index');
Route::view('/about', 'about')->name('about');
Route::view('/contact', 'contact')->name('contact');

Route::get('dashboard', function () {
    $user = Auth::user();
    
    // Redirect to role-specific dashboard
    $redirectUrl = match($user->role) {
        'admin' => '/admin/dashboard',
        'voter' => '/voter/dashboard',
        'candidate' => '/candidate/dashboard',
        default => '/admin/dashboard',
    };
    
    return redirect($redirectUrl);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/register', [RegisteredUserController::class, 'store']);

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'verified', 'admin'])->group(function(){
    Route::get('dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');

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
    
    // Announcements Routes (Updated)
    Route::get('announcement',[AnnouncementsController::class, 'index'])->name('admin.announcement'); // Redirect old route
    Route::get('announcements',[AnnouncementsController::class, 'index'])->name('admin.announcements');
    Route::post('announcements',[AnnouncementsController::class, 'store'])->name('admin.announcements.store');
    Route::put('announcements/{announcement}',[AnnouncementsController::class, 'update'])->name('admin.announcements.update');
    Route::delete('announcements/{announcement}',[AnnouncementsController::class, 'destroy'])->name('admin.announcements.destroy');
    Route::post('announcements/{announcement}/publish',[AnnouncementsController::class, 'publish'])->name('admin.announcements.publish');
    Route::post('announcements/{announcement}/unpublish',[AnnouncementsController::class, 'unpublish'])->name('admin.announcements.unpublish');
    
    // Position Routes
    Route::get('position',[PositionController::class, 'position'])->name('admin.position');
    Route::post('position',[PositionController::class, 'store'])->name('admin.position.store');
    Route::put('position/{position}',[PositionController::class, 'update'])->name('admin.position.update');
    Route::delete('position/{position}',[PositionController::class, 'destroy'])->name('admin.position.destroy');
});

// Voter Routes
Route::prefix('voter')->middleware(['auth', 'verified', 'voter'])->group(function(){
    Route::get('dashboard', [DashboardController::class, 'voterDashboard'])->name('voter.dashboard');

    Route::get('profile', [ProfileController::class, 'profile'])->name('voter.profile');
    Route::post('profile/photo', [ProfileController::class, 'updatePhoto'])->name('voter.profile.photo');
    Route::put('profile', [ProfileController::class, 'updateInfo'])->name('voter.profile.update');
    
    // Voting Routes
    Route::get('vote', [VotingController::class, 'index'])->name('voter.vote');
    Route::post('vote', [VotingController::class, 'store'])->name('voter.vote.store');
    Route::get('receipt', [VotingController::class, 'receipt'])->name('voter.receipt');
    
    // View Candidates
    Route::get('candidates', [VotingController::class, 'viewCandidates'])->name('voter.candidates');
    
    // View Announcements
    Route::get('announcements', [AnnouncementsController::class, 'voterIndex'])->name('voter.announcements');
    
    // View Results
    Route::get('result', [ResultController::class, 'voterResult'])->name('voter.result');
});

// Candidate Routes
Route::prefix('candidate')->middleware(['auth', 'verified', 'candidate'])->group(function(){
    Route::get('dashboard', [CandidateController::class, 'dashboard'])->name('candidate.dashboard');
    Route::get('profile', [CandidateController::class, 'profile'])->name('candidate.profile');
    Route::post('profile/photo', [CandidateController::class, 'updatePhoto'])->name('candidate.profile.photo');
    Route::post('profile/platform', [CandidateController::class, 'updatePlatform'])->name('candidate.profile.platform');
    Route::get('announcements', [CandidateController::class, 'announcements'])->name('candidate.announcements');
    Route::get('results', [CandidateController::class, 'results'])->name('candidate.results');
    Route::get('settings', [CandidateController::class, 'settings'])->name('candidate.settings');
    Route::put('settings/profile', [CandidateController::class, 'updateProfile'])->name('candidate.settings.profile');
    Route::put('settings/password', [CandidateController::class, 'updatePassword'])->name('candidate.settings.password');
});

require __DIR__.'/settings.php';

// Test route for voter search
if (config('app.env') !== 'production') {
    require __DIR__.'/test.php';
}


