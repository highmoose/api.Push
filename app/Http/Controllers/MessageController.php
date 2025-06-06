<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // ✅ GET /api/conversations
    public function conversations()
    {
        $userId = Auth::id();

        // Get distinct user IDs from message exchanges
        $conversationUserIds = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->get()
            ->map(function ($msg) use ($userId) {
                return $msg->sender_id === $userId ? $msg->receiver_id : $msg->sender_id;
            })
            ->unique()
            ->values();

        // Map each user to a conversation summary
        $conversations = $conversationUserIds->map(function ($otherUserId) use ($userId) {
            $user = User::find($otherUserId);

            $lastMessage = Message::where(function ($query) use ($userId, $otherUserId) {
                    $query->where('sender_id', $userId)
                          ->where('receiver_id', $otherUserId);
                })
                ->orWhere(function ($query) use ($userId, $otherUserId) {
                    $query->where('sender_id', $otherUserId)
                          ->where('receiver_id', $userId);
                })
                ->latest()
                ->first();

            $unreadCount = Message::where('sender_id', $otherUserId)
                ->where('receiver_id', $userId)
                ->whereNull('read_at')
                ->count();

            return [
                'user' => $user,
                'last_message' => $lastMessage,
                'unread_count' => $unreadCount,
            ];
        });

        return response()->json($conversations);
    }
    public function getAllMessages()
    {
        $authId = Auth::id();

        $messages = Message::where('sender_id', $authId)
            ->orWhere('receiver_id', $authId)
            ->orderBy('created_at')
            ->get();

        return response()->json($messages);
    }

    // ✅ GET /api/messages/{userId}
    public function getMessages($userId)
    {
        $authId = Auth::id();

        $messages = Message::where(function ($query) use ($authId, $userId) {
                $query->where('sender_id', $authId)
                      ->where('receiver_id', $userId);
            })
            ->orWhere(function ($query) use ($authId, $userId) {
                $query->where('sender_id', $userId)
                      ->where('receiver_id', $authId);
            })
            ->orderBy('created_at')
            ->get();

        // Mark received messages as read
        Message::where('sender_id', $userId)
            ->where('receiver_id', $authId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json($messages);
    }

    // ✅ POST /api/messages
    public function sendMessage(Request $request)
    {
        \Log::info('📥 Incoming message:', $request->all()); // Log message to storage/logs/laravel.log

        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string|max:10000',
        ]);

        $authUser = Auth::user();
        $receiverId = $request->receiver_id;

        // Authorisation check
        if (!$this->canMessage($authUser, $receiverId)) {
            return response()->json(['message' => 'You are not authorised to message this user.'], 403);
        }

        $message = Message::create([
            'sender_id' => $authUser->id,
            'receiver_id' => $receiverId,
            'content' => $request->content,
        ]);

        return response()->json($message, 201);
    }

    // 🔐 Messaging authorisation logic
    protected function canMessage($sender, $receiverId)
        {
            $receiver = User::find($receiverId);
            if (!$receiver) {
                return false;
            }

            switch ($sender->role) {
                case 'client':
                    // Clients can message their assigned trainer(s)
                    return $sender->trainers()->where('users.id', $receiver->id)->exists();

                case 'trainer':
                    // Trainers can message their clients or gym owners
                    return $sender->clients()->where('users.id', $receiver->id)->exists()
                        || $receiver->role === 'gym_owner';

                case 'gym_owner':
                    // Gym owners can message trainers
                    return $receiver->role === 'trainer';

                default:
                    return false;
            }
        }

}
