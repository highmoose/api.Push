<?php

namespace App\Http\Controllers;

use App\Models\UseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'role' => 'required|string|in:client,trainer,gym_owner,admin',
        ]);

        $user = UserModel::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'role' => $fields['role']
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;
       
        return response([
            'message' => 'Registration successful.',
            'user' => $user,
            'token' => $token
        ], 201);
    }
}