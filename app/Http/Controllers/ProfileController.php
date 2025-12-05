<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Election;

class ProfileController extends Controller
{
    public function profile() {
        $user = Auth::user();
        $voterProfile = \DB::table('voter_profiles')->where('user_id', $user->id)->first();
        $activeElection = Election::where('is_active', 1)->first();
        
        // Check if user has voted in active election
        $hasVoted = false;
        if ($activeElection) {
            $hasVoted = \DB::table('votes')
                ->where('user_id', $user->id)
                ->where('election_id', $activeElection->id)
                ->exists();
        }
        
        return Inertia::render('voter/Profile', [
            'voter' => [
                'id' => $user->id,
                'student_id' => $voterProfile->student_id ?? '',
                'name' => $user->name,
                'email' => $user->email,
                'program' => $voterProfile->course ?? '',
                'year' => $voterProfile->year_level ?? '',
                'section' => $voterProfile->section ?? '',
                'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                'voted' => $hasVoted
            ]
        ]);
    }
    
    public function updatePhoto(Request $request) {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png|max:2048'
        ]);
        
        $user = Auth::user();
        
        // Delete old photo if exists
        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }
        
        // Store new photo
        $path = $request->file('photo')->store('voters', 'public');
        
        $user->update(['photo' => $path]);
        
        // Return updated user with photo URL
        return back()->with([
            'success' => 'Photo updated successfully',
            'photo' => asset('storage/' . $path)
        ]);
    }
    
    public function updateInfo(Request $request) {
        $request->validate([
            'student_id' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
            'program' => 'required|string|in:BSIT,BSIS',
            'year' => 'required|string|in:1st Year,2nd Year,3rd Year,4th Year',
            'section' => 'nullable|string|max:10'
        ]);
        
        $user = Auth::user();
        
        // Update users table
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);
        
        // Update voter_profiles table
        \DB::table('voter_profiles')
            ->where('user_id', $user->id)
            ->update([
                'student_id' => $request->student_id,
                'course' => $request->program,
                'year_level' => $request->year,
                'section' => $request->section,
                'updated_at' => now()
            ]);
        
        return back()->with('success', 'Profile updated successfully');
    }
}