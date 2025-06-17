<?php

namespace App\Http\Controllers;

use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string', 
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'role' => 'required|string|in:client,trainer,gym_owner,admin',
        ]);

        $user = UserModel::create([
            'first_name' => $fields['first_name'],
            'last_name' => $fields['last_name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
            'role' => $fields['role']
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
       
        return response([
            'message' => 'Registration successful.',
            'user' => $user,
            'token' => $token
        ], 201);
    }
}