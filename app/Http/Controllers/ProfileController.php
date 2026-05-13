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
        
        // Use our new View to fetch all profile details in one query
        $voter = DB::table('view_voter_details')
            ->where('user_id', $user->id)
            ->first();
        
        if (!$voter) {
            // Handle case where user is not a voter (e.g. admin)
            return Inertia::render('voter/Profile', [
                'voter' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                    'role' => $user->role
                ]
            ]);
        }
        
        return Inertia::render('voter/Profile', [
            'voter' => [
                'id' => $voter->user_id,
                'student_id' => $voter->student_id,
                'name' => $voter->name,
                'email' => $voter->email,
                'program' => $voter->course,
                'year' => $voter->year_level,
                'section' => $voter->section,
                'photo' => $voter->photo ? asset('storage/' . $voter->photo) : null,
                'voted' => (bool) $voter->has_voted_active
            ]
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
        
        try {
            // Call the Stored Procedure to handle the multi-table update
            DB::statement('CALL sp_UpdateUserProfile(?, ?, ?, ?, ?, ?, ?)', [
                Auth::id(),
                $request->name,
                $request->email,
                $request->student_id,
                $request->program,
                $request->year,
                $request->section ?? ''
            ]);

            return back()->with('success', 'Profile updated successfully');
        } catch (\Exception $e) {
            \Log::error('Profile update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update profile.']);
        }
    }
}