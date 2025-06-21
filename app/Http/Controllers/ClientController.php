<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\UserModel;

class ClientController extends Controller
{
    // GET /api/client/trainer - Get trainer info for the authenticated client
    public function getTrainer()
    {
        $client = Auth::user();
        
        if ($client->role !== 'client') {
            return response()->json(['error' => 'Only clients can access this endpoint'], 403);
        }

        // Find the trainer through the trainer_client relationship
        $trainer = $client->trainers()->first();
        
        if (!$trainer) {
            return response()->json(['error' => 'No trainer assigned'], 404);
        }

        return response()->json([
            'trainer' => $trainer
        ]);
    }

    // POST /api/clients - Create a new client (for trainers)
    public function store(Request $request)
    {
        $trainer = Auth::user();
        
        if ($trainer->role !== 'trainer') {
            return response()->json(['error' => 'Only trainers can create clients'], 403);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'gym' => 'nullable|string|max:255',
            'height' => 'nullable|numeric|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:500',
            'fitness_goals' => 'nullable|string|max:255',
            'fitness_experience' => 'nullable|string|max:255',
            'fitness_level' => 'nullable|string|max:255',
            'measurements' => 'nullable|string',
            'food_likes' => 'nullable|string',
            'food_dislikes' => 'nullable|string',
            'allergies' => 'nullable|string',
            'medical_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Create the client with a temporary password
            $client = UserModel::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make(Str::random(12)), // Temporary password
                'role' => 'client',
                'is_temp' => 1, // Mark as temporary until they set their own password
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'address' => $request->address,
                'gym' => $request->gym,
                'height' => $request->height,
                'weight' => $request->weight,
                'fitness_goals' => $request->fitness_goals,
                'fitness_experience' => $request->fitness_experience,
                'fitness_level' => $request->fitness_level,
                'measurements' => $request->measurements,
                'food_likes' => $request->food_likes,
                'food_dislikes' => $request->food_dislikes,
                'allergies' => $request->allergies,
                'medical_conditions' => $request->medical_conditions,
                'notes' => $request->notes,
            ]);

            // Link the client to the trainer
            $trainer->clients()->attach($client->id);

            return response()->json([
                'success' => true,
                'message' => 'Client created successfully',
                'client' => $client
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create client',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // POST /api/clients/invite - Generate and send client invitation
    public function sendInvite(Request $request)
    {
        $trainer = Auth::user();
        
        if ($trainer->role !== 'trainer') {
            return response()->json(['error' => 'Only trainers can send invites'], 403);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Generate unique invite token
            $inviteToken = Str::random(64);
            
            // Check if user already exists
            $existingUser = UserModel::where('email', $request->email)->first();
            if ($existingUser && !$existingUser->is_temp) {
                return response()->json([
                    'success' => false,
                    'message' => 'A user with this email already exists'
                ], 409);
            }

            // Create or update the invite record
            $client = UserModel::updateOrCreate(
                ['email' => $request->email],
                [
                    'invite_token' => $inviteToken,
                    'invite_sent_at' => now(),
                    'is_temp' => 1,
                    'role' => 'client',
                    'password' => Hash::make(Str::random(12)) // Temporary password
                ]
            );

            // Link to trainer if not already linked
            if (!$trainer->clients()->where('client_id', $client->id)->exists()) {
                $trainer->clients()->attach($client->id);
            }

            // Generate the invitation link
            $baseUrl = config('app.frontend_url', 'http://localhost:3000');
            $inviteLink = "{$baseUrl}/register?invite_token={$inviteToken}&trainer_id={$trainer->id}";

            // Send invitation email (we'll implement this based on your preference)
            $this->sendInviteEmail($request->email, $inviteLink, $trainer);

            return response()->json([
                'success' => true,
                'message' => 'Invitation sent successfully',
                'invite_link' => $inviteLink
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send invitation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // POST /api/clients/accept-invite - Accept client invitation and complete registration
    public function acceptInvite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invite_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'height' => 'nullable|numeric|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:500',
            'fitness_goals' => 'nullable|string|max:255',
            'fitness_experience' => 'nullable|string|max:255',
            'fitness_level' => 'nullable|string|max:255',
            'measurements' => 'nullable|string',
            'food_likes' => 'nullable|string',
            'food_dislikes' => 'nullable|string',
            'allergies' => 'nullable|string',
            'medical_conditions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $client = UserModel::where('invite_token', $request->invite_token)
                              ->where('is_temp', 1)
                              ->first();

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired invitation token'
                ], 404);
            }

            // Update client with their information
            $client->update([
                'password' => Hash::make($request->password),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'address' => $request->address,
                'height' => $request->height,
                'weight' => $request->weight,
                'fitness_goals' => $request->fitness_goals,
                'fitness_experience' => $request->fitness_experience,
                'fitness_level' => $request->fitness_level,
                'measurements' => $request->measurements,
                'food_likes' => $request->food_likes,
                'food_dislikes' => $request->food_dislikes,
                'allergies' => $request->allergies,
                'medical_conditions' => $request->medical_conditions,
                'is_temp' => 0, // No longer temporary
                'invite_token' => null, // Clear the token
                'invite_accepted_at' => now(),
                'email_verified_at' => now(), // Auto-verify email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registration completed successfully',
                'client' => $client
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete registration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // PUT /api/clients/{id} - Update client information
    public function update(Request $request, $id)
    {
        $trainer = Auth::user();
        
        if ($trainer->role !== 'trainer') {
            return response()->json(['error' => 'Only trainers can update clients'], 403);
        }

        // Verify the client belongs to this trainer
        $client = $trainer->clients()->find($id);
        if (!$client) {
            return response()->json(['error' => 'Client not found or not associated with this trainer'], 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'gym' => 'nullable|string|max:255',
            'height' => 'nullable|numeric|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:500',
            'fitness_goals' => 'nullable|string|max:255',
            'fitness_experience' => 'nullable|string|max:255',
            'fitness_level' => 'nullable|string|max:255',
            'measurements' => 'nullable|string',
            'food_likes' => 'nullable|string',
            'food_dislikes' => 'nullable|string',
            'allergies' => 'nullable|string',
            'medical_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $client->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Client updated successfully',
                'client' => $client
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update client',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Private method to send invitation email
    private function sendInviteEmail($email, $inviteLink, $trainer)
    {
        // For now, we'll implement a simple version
        // You can later integrate with Resend or Laravel's built-in mail
          try {
            // If you want to use Resend, we can implement it here
            // For now, let's just log the email details
            Log::info('Client invitation email', [
                'to' => $email,
                'trainer' => $trainer->first_name . ' ' . $trainer->last_name,
                'invite_link' => $inviteLink
            ]);

            // TODO: Implement actual email sending
            // Mail::to($email)->send(new ClientInvitationMail($inviteLink, $trainer));
            
        } catch (\Exception $e) {
            Log::error('Failed to send invite email: ' . $e->getMessage());
            throw $e;
        }
    }
}
