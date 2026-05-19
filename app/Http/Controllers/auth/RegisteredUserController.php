<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
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

        try {
            // Call the stored procedure sp_RegisterVoter
            // We hash the password in PHP before passing it to the database
            DB::statement('CALL sp_RegisterVoter(?, ?, ?, ?)', [
                $request->name,
                $request->email,
                Hash::make($request->password),
                $request->student_id
            ]);

            // Fetch the newly created user record to log them in
            $userRecord = DB::table('users')->where('email', $request->email)->first();

            if (!$userRecord) {
                throw new \Exception('User creation failed: Record not found after procedure call.');
            }

            Log::info('User registered via stored procedure', ['user_id' => $userRecord->id]);

            // Auto-login the user using their ID
            Auth::loginUsingId($userRecord->id);

            return response()->json([
                'message' => 'Registration successful.',
                'user'    => $userRecord,
                'redirect' => '/voter/dashboard',
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle errors from the stored procedure (e.g., Whitelist failure)
            Log::error('Registration via procedure failed', [
                'error' => $e->getMessage(),
                'student_id' => $request->student_id
            ]);

            $errorMessage = 'Registration failed. Please try again.';
            if ($e->getCode() == '45000') {
                // QueryException appends the SQL query to the message, which we don't want to expose.
                // We just want the clean message.
                $errorMessage = 'Student ID not recognized in whitelist.';
            }

            // Return validation exception for recognized student_id errors
            if (str_contains($errorMessage, 'Student ID not recognized')) {
                throw ValidationException::withMessages([
                    'student_id' => 'Student ID not recognized in our database. Please make sure you are an approved student.',
                ]);
            }

            return response()->json([
                'error' => $errorMessage,
            ], 500);

        } catch (\Exception $e) {
            Log::error('Unexpected registration error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'An unexpected error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
}
