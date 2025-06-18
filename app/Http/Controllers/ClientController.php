<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
}
