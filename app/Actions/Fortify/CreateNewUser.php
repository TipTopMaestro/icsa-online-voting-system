<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): \Illuminate\Contracts\Auth\Authenticatable
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                \Illuminate\Validation\Rule::unique('users'),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $userId = \Illuminate\Support\Facades\DB::table('users')->insertGetId([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return \App\Models\User::find($userId);
    }
}
