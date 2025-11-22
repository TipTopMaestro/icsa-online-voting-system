<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\ApprovedStudent;
use App\Models\VoterProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class RegisterUser
{
    public function register(array $input)
    {
        // 1. Validate input
        Validator::make($input, [
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'   => ['required', 'string', 'min:8'],
            'student_id' => ['required', 'string'],
        ])->validate();

        // 2. Check if student exists in approved_students
        $approved = ApprovedStudent::where('student_id', $input['student_id'])->first();

        if (!$approved) {
            return response([
                'error' => 'Your student ID is not recognized. Only BSIT/BSIS students may register.'
            ], 422);
        }

        // 3. Create user (role = voter only)
        $user = User::create([
            'name'       => $input['name'],
            'email'      => $input['email'],
            'password'   => Hash::make($input['password']),
            'role'       => 'voter',
        ]);

        // 4. Create voter profile
        VoterProfile::create([
            'user_id'    => $user->id,
            'student_id' => $approved->student_id,
            'course'     => $approved->course,
            'year_level' => $approved->year_level,
            'section'    => $approved->section,
            'has_voted'  => false,
        ]);

        return $user;
    }
}
