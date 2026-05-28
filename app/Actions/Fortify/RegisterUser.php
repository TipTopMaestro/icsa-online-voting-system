<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


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

        // 2. Call the stored procedure sp_RegisterVoter
        // This handles whitelist checking and double-insert into users/profiles
        try {
            DB::statement('CALL sp_RegisterVoter(?, ?, ?, ?)', [
                $input['name'],
                $input['email'],
                Hash::make($input['password']),
                $input['student_id']
            ]);

            // 3. Fetch the newly created user record to return
            $user = DB::table('users')->where('email', $input['email'])->first();

            return $user;
        } catch (\Exception $e) {
            // Check for whitelist failure from SQL SIGNAL
            if (str_contains($e->getMessage(), 'Student ID not recognized')) {
                return response([
                    'error' => 'Your student ID is not recognized. Only BSIT/BSIS students may register.'
                ], 422);
            }
            throw $e;
        }
    }
}
