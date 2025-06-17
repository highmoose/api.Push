<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TrainerController extends Controller
{
    public function clients(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'trainer') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $query = $user->clients();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $clients = $query->get()->makeHidden(['pivot']);

        return response()->json(['clients' => $clients]);
    }

    public function addClient(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'trainer') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:6',
        ]);

        $client = UserModel::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'] ?? null,
            'email'      => $validated['email'],
            'password'   => bcrypt($validated['password']),
            'role'       => 'client',
        ]);

        $user->clients()->attach($client->id);

        return response()->json([
            'message' => 'Client created and linked successfully.',
            'client'  => $client->makeHidden(['pivot']),
        ]);
    }

    public function addTempClient(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'trainer') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'email'      => 'nullable|email|unique:users,email',
        ]);

        $client = UserModel::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'] ?? null,
            'email'      => $validated['email'] ?? null,
            'password'   => bcrypt(Str::random(16)),
            'role'       => 'client',
            'is_temp'    => true,
        ]);

        $user->clients()->syncWithoutDetaching([$client->id]);

        return response()->json([
            'message' => 'Temporary client created and linked successfully.',
            'client'  => $client->makeHidden(['pivot']),
        ]);
    }

    public function upgradeTempClient(Request $request, $id)
    {
        $client = UserModel::findOrFail($id);

        if (!$client->is_temp) {
            return response()->json(['message' => 'Not a temp client'], 400);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $client->id,
            'password'   => 'required|min:8',
        ]);

        $client->update([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'] ?? null,
            'email'      => $validated['email'],
            'password'   => bcrypt($validated['password']),
            'role'       => 'client',
            'is_temp'    => false,
        ]);

        return response()->json([
            'message' => 'Client upgraded successfully.',
            'client'  => $client,
        ]);
    }

    public function getClient($id)
    {
        $user = Auth::user();

        if ($user->role !== 'trainer') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $client = $user->clients()->where('id', $id)->first();

        if (!$client) {
            return response()->json(['message' => 'Client not found or not linked to you'], 404);
        }

        return response()->json(['client' => $client->makeHidden(['pivot'])]);
    }

    public function updateClient(Request $request, $id)
    {
        $user = Auth::user();

        if ($user->role !== 'trainer') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // ✅ Fix: Fully qualify `users.id`
        $client = $user->clients()->where('users.id', $id)->first();

        if (!$client) {
            return response()->json(['message' => 'Client not found or not linked to you'], 404);
        }

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name'  => 'sometimes|string|max:255',
            'email'      => 'sometimes|email|unique:users,email,' . $client->id,
            'password'   => 'sometimes|string|min:6',
            'location'   => 'sometimes|string|max:255',
            'gym'        => 'sometimes|string|max:255',
            'phone'      => 'sometimes|string|max:255',
            'date_of_birth' => 'sometimes|date',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $client->update($validated);

        return response()->json([
            'message' => 'Client updated successfully.',
            'client'  => $client->makeHidden(['pivot']),
        ]);
    }

    public function deleteClient($id)
    {
        $user = Auth::user();

        if ($user->role !== 'trainer') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $client = $user->clients()->where('users.id', $id)->first();

        if (!$client) {
            return response()->json(['message' => 'Client not found or not linked to you'], 404);
        }

        $user->clients()->detach($client->id);

        return response()->json(['message' => 'Client unlinked successfully.']);
    }
}
