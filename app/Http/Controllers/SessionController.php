<?php

namespace App\Http\Controllers;

use App\Models\SessionModel;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class SessionController extends Controller
{
    // POST /api/sessions
    public function create(Request $request)
    {
        $request->validate([
            'client_id'          => 'required|exists:users,id',
            'scheduled_at'       => 'required|date',
            'duration'           => 'required|integer|min:1',
            'gym_id'             => 'nullable|exists:gyms,id',
            'notes'              => 'nullable|string|max:1000',
            'status'             => 'nullable|in:scheduled,pending,completed,cancelled',
            'session_type'       => 'nullable|string|max:100',
            'location'           => 'nullable|string|max:255',
            'rate'               => 'nullable|numeric|min:0',
            'equipment_needed'   => 'nullable|string|max:1000',
            'preparation_notes'  => 'nullable|string|max:1000',
            'goals'              => 'nullable|string|max:1000',
        ]);        $authUser = Auth::user();
        $clientId = $request->client_id;
        $userTimezone = $authUser->timezone ?? config('app.default_user_timezone', 'Europe/London');

        // Load the client to check relationship
        $client = User::findOrFail($clientId);

        if ($authUser->role === 'trainer') {

            // Allow status override or default to 'scheduled'
            $status = $request->status ?? 'scheduled';
            if (!in_array($status, ['scheduled', 'pending', 'completed', 'cancelled'])) {
                return response()->json(['error' => 'Invalid session status'], 422);
            }

            $trainerId = $authUser->id;

        } elseif ($authUser->role === 'client') {
            // Client can only create for themselves
            if ($authUser->id !== $clientId) {
                return response()->json(['error' => 'Clients can only request sessions for themselves'], 403);
            }

            // Must have a trainer assigned
            if (!$authUser->trainer_id) {
                return response()->json(['error' => 'No trainer assigned'], 403);
            }

            $trainerId = $authUser->trainer_id;
            $status = 'pending';
        } else {
            return response()->json(['error' => 'Only clients and trainers can create sessions'], 403);
        }

        // Parse the scheduled_at time from user's timezone and convert to UTC for storage
        $scheduledAtUTC = null;
        if ($request->scheduled_at) {
            try {
                $dt = new \DateTime($request->scheduled_at, new \DateTimeZone($userTimezone));
                $dt->setTimezone(new \DateTimeZone('UTC'));
                $scheduledAtUTC = $dt->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid datetime format'], 422);
            }
        }

        $session = SessionModel::create([
            'trainer_id'         => $trainerId,
            'client_id'          => $clientId,
            'gym_id'             => $request->gym_id,
            'start_time'         => $scheduledAtUTC,
            'end_time'           => $scheduledAtUTC ? Carbon::parse($scheduledAtUTC)->addMinutes((int) $request->duration) : null,
            'status'             => $status,
            'notes'              => $request->notes,
            'session_type'       => $request->session_type ?? 'general',
            'location'           => $request->location,
            'rate'               => $request->rate ?? 0,
            'equipment_needed'   => $request->equipment_needed,
            'preparation_notes'  => $request->preparation_notes,
            'goals'              => $request->goals,
            'duration'           => $request->duration,
        ]);

        // Load the client relationship for the response
        $session->load('client:id,first_name,last_name');

        // Return the session with all fields including client info
        return response()->json([
            'id'                => $session->id,
            'client_id'         => $session->client_id,
            'trainer_id'        => $session->trainer_id,
            'start_time'        => $session->start_time,
            'end_time'          => $session->end_time,
            'status'            => $session->status,
            'notes'             => $session->notes,
            'session_type'      => $session->session_type,
            'location'          => $session->location,
            'rate'              => $session->rate,
            'equipment_needed'  => $session->equipment_needed,
            'preparation_notes' => $session->preparation_notes,
            'goals'             => $session->goals,
            'duration'          => $session->duration,
            'first_name'        => $session->client->first_name,
            'last_name'         => $session->client->last_name,
            'created_at'        => $session->created_at,
            'updated_at'        => $session->updated_at,
        ], 201);    }

    // GET /api/sessions (trainer)
    public function index(Request $request)
    {
        $user = Auth::user();
        $trainerId = $user->id;
        $userTimezone = $user->timezone ?? config('app.default_user_timezone', 'Europe/London');
        $includePast = $request->query('include_past', false);

        $query = SessionModel::with(['client:id,first_name,last_name,gym,timezone']) // include client timezone
            ->where('trainer_id', $trainerId);

        // if (!$includePast) {
        //     $query->where('start_time', '>=', now())
        //           ->where('status', '!=', 'cancelled');
        // }

        $sessions = $query->orderBy('start_time')->get()->map(function ($session) use ($userTimezone) {
            // Convert times to user's timezone for display
            $startTime = $session->start_time ? $session->start_time->setTimezone($userTimezone)->format('Y-m-d H:i:s') : null;
            $endTime = $session->end_time ? $session->end_time->setTimezone($userTimezone)->format('Y-m-d H:i:s') : null;

            return [
                'id'                => $session->id,
                'client_id'         => $session->client->id,
                'trainer_id'        => $session->trainer_id,
                'start_time'        => $startTime,
                'end_time'          => $endTime,
                'status'            => $session->status,
                'notes'             => $session->notes,
                'session_type'      => $session->session_type,
                'location'          => $session->location,
                'rate'              => $session->rate,
                'equipment_needed'  => $session->equipment_needed,
                'preparation_notes' => $session->preparation_notes,
                'goals'             => $session->goals,
                'duration'          => $session->duration,
                'first_name'        => $session->client->first_name,
                'last_name'         => $session->client->last_name,
                'gym'               => $session->client->gym,
                'user_timezone'     => $userTimezone, // Include timezone info for frontend
            ];
        });

        return response()->json($sessions);
    }


    // GET /api/sessions/client
    public function clientSessions()
    {
        $clientId = Auth::id();

        $sessions = SessionModel::where('client_id', $clientId)
            ->where('start_time', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_time')
            ->get();

        return response()->json($sessions);
    }

    // PUT /api/sessions/{id}
    // PUT /api/sessions/{id}/status - Update only status without touching datetime
    public function updateStatus(Request $request, $id)
    {
        $session = SessionModel::findOrFail($id);
        $userId = Auth::id();

        if ($session->trainer_id !== $userId && $session->client_id !== $userId) {
            return response()->json(['error' => 'Unauthorised'], 403);
        }

        $request->validate([
            'status' => 'required|in:scheduled,completed,pending,cancelled',
        ]);

        // Only update the status field, don't touch any datetime fields
        $session->status = $request->status;
        $session->save();

        // Return minimal response to avoid datetime serialization issues
        return response()->json([
            'id' => $session->id,
            'status' => $session->status,
            'message' => 'Status updated successfully'
        ]);
    }

    public function update(Request $request, $id)
    {
        $session = SessionModel::findOrFail($id);
        $userId = Auth::id();

        if ($session->trainer_id !== $userId && $session->client_id !== $userId) {
            return response()->json(['error' => 'Unauthorised'], 403);
        }

        $request->validate([
            'scheduled_at' => 'nullable|date',
            'duration'     => 'nullable|integer|min:1',
            'status'       => 'nullable|in:scheduled,completed,pending,cancelled',
            'gym_id'       => 'nullable|exists:gyms,id',
            'notes'        => 'nullable|string|max:1000',
        ]);        // Handle datetime updates with timezone conversion
        if ($request->filled('scheduled_at') && $request->filled('duration')) {
            try {
                // Parse the datetime from user's timezone and convert to UTC for storage
                $user = Auth::user();
                $userTimezone = $user->timezone ?? config('app.default_user_timezone', 'Europe/London');
                
                $dt = new \DateTime($request->scheduled_at, new \DateTimeZone($userTimezone));
                $dt->setTimezone(new \DateTimeZone('UTC'));
                $scheduledAtUTC = $dt->format('Y-m-d H:i:s');
                
                $session->start_time = $scheduledAtUTC;
                $session->end_time = Carbon::parse($scheduledAtUTC)->addMinutes($request->duration);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid datetime format'], 422);
            }
        }

        if ($request->has('status')) {
            $session->status = $request->status;
        }

        if ($request->has('gym_id')) {
            $session->gym_id = $request->gym_id;
        }

        if ($request->has('notes')) {
            $session->notes = $request->notes;
        }        $session->save();        // Load the client relationship for the response
        $session->load('client:id,first_name,last_name');

        // Get user timezone for response
        $user = Auth::user();
        $userTimezone = $user->timezone ?? config('app.default_user_timezone', 'Europe/London');

        // Convert times back to user's timezone for response
        $startTimeUser = $session->start_time ? $session->start_time->setTimezone($userTimezone)->format('Y-m-d H:i:s') : null;
        $endTimeUser = $session->end_time ? $session->end_time->setTimezone($userTimezone)->format('Y-m-d H:i:s') : null;

        // Return the session with all fields including client info
        return response()->json([
            'id'                => $session->id,
            'client_id'         => $session->client_id,
            'trainer_id'        => $session->trainer_id,
            'start_time'        => $startTimeUser,
            'end_time'          => $endTimeUser,
            'status'            => $session->status,
            'notes'             => $session->notes,
            'session_type'      => $session->session_type,
            'location'          => $session->location,
            'rate'              => $session->rate,
            'equipment_needed'  => $session->equipment_needed,
            'preparation_notes' => $session->preparation_notes,
            'goals'             => $session->goals,
            'duration'          => $session->duration,
            'first_name'        => $session->client->first_name,
            'last_name'         => $session->client->last_name,
            'created_at'        => $session->created_at,
            'updated_at'        => $session->updated_at,
            'user_timezone'     => $userTimezone,
        ]);
    }

    // DELETE /api/sessions/{id}
    public function cancel($id)
    {
        $session = SessionModel::findOrFail($id);
        $userId = Auth::id();

        if ($session->trainer_id !== $userId && $session->client_id !== $userId) {
            return response()->json(['error' => 'Unauthorised'], 403);
        }

        $session->status = 'cancelled';
        $session->save();

        return response()->json(['message' => 'Session cancelled.']);
    }

    // DELETE /api/sessions/{id}/delete - Permanently delete session
    public function destroy($id)
    {
        $session = SessionModel::findOrFail($id);
        $userId = Auth::id();

        // Only trainer should be able to permanently delete sessions
        if ($session->trainer_id !== $userId) {
            return response()->json(['error' => 'Only the trainer can delete sessions'], 403);
        }

        $session->delete();

        return response()->json(['message' => 'Session deleted successfully.']);
    }
}
