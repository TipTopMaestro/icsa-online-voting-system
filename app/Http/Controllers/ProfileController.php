<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function profile() {
        $user = Auth::user();
        
        // Use our new View to fetch all profile details in one query
        $voter = DB::table('view_voter_details')
            ->where('user_id', $user->id)
            ->first();
        
        if (!$voter) {
            return Inertia::render('voter/Profile', [
                'voter' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->photo ? asset('storage/' . $user->photo) : null,
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
                'avatar' => $voter->photo ? asset('storage/' . $voter->photo) : null,
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
            'section' => 'nullable|string|max:10',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);
        
        try {
            $user = Auth::user();
            $photoPath = $this->handlePhotoUpload($request, $user->getRawOriginal('photo'));

            $this->executeProfileUpdate([
                'user_id' => $user->id,
                'name' => $request->name,
                'email' => $request->email,
                'student_id' => $request->student_id,
                'program' => $request->program,
                'year' => $request->year,
                'section' => $request->section ?? '',
                'photo' => $photoPath
            ]);

            return back()->with('success', 'Profile updated successfully');
        } catch (\Exception $e) {
            \Log::error('Profile update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update profile.']);
        }
    }

    public function updatePhoto(Request $request)
    {
        $request->validate(['photo' => 'required|image|mimes:jpeg,png,jpg|max:2048']);

        try {
            $user = Auth::user();
            $voter = DB::table('voter_profiles')->where('user_id', $user->id)->first();

            if (!$voter) return back()->withErrors(['photo' => 'Voter profile not found.']);

            $photoPath = $this->handlePhotoUpload($request, $user->getRawOriginal('photo'));

            $this->executeProfileUpdate([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'student_id' => $voter->student_id,
                'program' => $voter->course,
                'year' => $voter->year_level,
                'section' => $voter->section,
                'photo' => $photoPath
            ]);

            return back()->with('success', 'Profile photo updated successfully');
        } catch (\Exception $e) {
            \Log::error('Photo update failed: ' . $e->getMessage());
            return back()->withErrors(['photo' => 'Failed to upload photo.']);
        }
    }

    private function handlePhotoUpload(Request $request, $oldPath) {
        if (!$request->hasFile('photo')) return $oldPath;

        if ($oldPath) Storage::disk('public')->delete($oldPath);
        return $request->file('photo')->store('profiles', 'public');
    }

    private function executeProfileUpdate(array $data) {
        DB::statement('CALL sp_UpdateUserProfile(?, ?, ?, ?, ?, ?, ?, ?)', [
            $data['user_id'],
            $data['name'],
            $data['email'],
            $data['student_id'],
            $data['program'],
            $data['year'],
            $data['section'],
            $data['photo']
        ]);
    }
}