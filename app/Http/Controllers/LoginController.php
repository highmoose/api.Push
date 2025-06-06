<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function store(Request $request)
        {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                return response([
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $request->session()->regenerate();

            return response([
                'message' => 'Login successful',
                'user' => Auth::user(),
                'role' => Auth::user()->role
            ]);
        }

    public function login(Request $request)
        {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // if ($user->role === 'trainer') {
        //     $user->load('clients');
        // }

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'role' => $user->role,
            // 'clients' => $user->role === 'trainer' ? $user->clients : null,
        ]);
    }
}