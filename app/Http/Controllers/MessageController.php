<?php

namespace App\Http\Controllers;

use App\Models\MessageModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // ✅ GET /api/conversations
    public function conversations()
    {
        $userId = Auth::id();

        $conversationUserIds = MessageModel::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->get()
            ->map(function ($msg) use ($userId) {
                return $msg->sender_id === $userId ? $msg->receiver_id : $msg->sender_id;
            })
            ->unique()
            ->values();

        $conversations = $conversationUserIds->map(function ($otherUserId) use ($userId) {
            $user = UserModel::find($otherUserId);

            $lastMessage = MessageModel::where(function ($query) use ($userId, $otherUserId) {
                    $query->where('sender_id', $userId)
                          ->where('receiver_id', $otherUserId);
                })
                ->orWhere(function ($query) use ($userId, $otherUserId) {
                    $query->where('sender_id', $otherUserId)
                          ->where('receiver_id', $userId);
                })
                ->latest()
                ->first();

            $unreadCount = MessageModel::where('sender_id', $otherUserId)
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

        $messages = MessageModel::where('sender_id', $authId)
            ->orWhere('receiver_id', $authId)
            ->orderBy('created_at')
            ->get();

        return response()->json($messages);
    }

    // ✅ GET /api/messages/{userId}
    public function getMessages($userId)
    {
        $authId = Auth::id();

        $messages = MessageModel::where(function ($query) use ($authId, $userId) {
                $query->where('sender_id', $authId)
                      ->where('receiver_id', $userId);
            })
            ->orWhere(function ($query) use ($authId, $userId) {
                $query->where('sender_id', $userId)
                      ->where('receiver_id', $authId);
            })
            ->orderBy('created_at')
            ->get();

        MessageModel::where('sender_id', $userId)
            ->where('receiver_id', $authId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json($messages);
    }

    // ✅ POST /api/messages
    public function sendMessage(Request $request)
    {
        \Log::info('📥 Incoming message:', $request->all());

        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string|max:10000',
        ]);

        $authUser = Auth::user();
        $receiverId = $request->receiver_id;

        if (!$this->canMessage($authUser, $receiverId)) {
            return response()->json(['message' => 'You are not authorised to message this user.'], 403);
        }

        $message = MessageModel::create([
            'sender_id' => $authUser->id,
            'receiver_id' => $receiverId,
            'content' => $request->content,
        ]);

        return response()->json($message, 201);
    }

    // 🔐 Messaging authorisation logic
    protected function canMessage($sender, $receiverId)
    {
        $receiver = UserModel::find($receiverId);
        if (!$receiver) {
            return false;
        }

        switch ($sender->role) {
            case 'client':
                return $sender->trainers()->where('users.id', $receiver->id)->exists();

            case 'trainer':
                return $sender->clients()->where('users.id', $receiver->id)->exists()
                    || $receiver->role === 'gym_owner';

            case 'gym_owner':
                return $receiver->role === 'trainer';

            default:
                return false;
        }
    }
}
