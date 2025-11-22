<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ApprovedStudent;
use App\Models\VoterProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;


class RegisteredUserController extends Controller
{
    public function store(Request $request)
    {
        // Validate incoming registration input
        $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
            'student_id' => ['required', 'string'],
        ]);

        // Check if student is in approved_students list
        $approved = ApprovedStudent::where('student_id', $request->student_id)->first();

        if (!$approved) {
            throw ValidationException::withMessages([
                'student_id' => 'Your student ID is not recognized. Only BSIT/BSIS students may register.',
            ]);
        }

        try {
            DB::beginTransaction();

            // Create user (role = voter)
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'voter',
            ]);

            Log::info('User created', ['user_id' => $user->id]);

            // Create voter profile linked to user
            $voterProfile = VoterProfile::create([
                'user_id'    => $user->id,
                'student_id' => $approved->student_id,
                'course'     => $approved->course,
                'year_level' => $approved->year_level,
                'section'    => $approved->section ?? 'N/A',
                'has_voted'  => false,
            ]);

            Log::info('Voter profile created', ['voter_profile_id' => $voterProfile->id]);

            DB::commit();

            // Auto-login the user
            Auth::login($user);

            return response()->json([
                'message' => 'Registration successful.',
                'user'    => $user,
                'redirect' => '/voter/dashboard',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'student_id' => $request->student_id,
                'user_id' => $user->id ?? null,
            ]);

            return response()->json([
                'error' => 'Registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
