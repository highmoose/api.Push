<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DietPlanController;

// User registration route
Route::post('/register', [RegisterController::class, 'register']);

// User login route
Route::post('/login', [LoginController::class, 'login']);

// Logout route, protected by auth:sanctum middleware since it requires an authenticated user
Route::middleware('auth:sanctum')->post('/logout', [LogoutController::class, 'logout']);

// Optional: A route to fetch the currently authenticated user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->prefix('trainer/clients')->controller(TrainerController::class)->group(function () {
    Route::get('/', 'clients');                        // List all clients for the authenticated trainer
    Route::post('/', 'addClient');                     // Add a new client
    Route::post('/addTemp', 'addTempClient');          // Add a temporary client (for testing purposes)
    Route::get('{id}', 'getClient');                   // Get a specific client by ID
    Route::put('{id}', 'updateClient');                // Update client details
    Route::delete('{id}', 'deleteClient');             // Delete a client
});

Route::middleware(['auth:sanctum'])->prefix('client')->controller(ClientController::class)->group(function () {
    Route::get('/trainer', 'getTrainer');              // Get trainer info for the authenticated client
});

Route::middleware('auth:sanctum')->controller(MessageController::class)->group(function () {
    Route::get('/conversations', 'conversations');     // List all conversations
    Route::get('/messages', 'getAllMessages');         // Get all messages for the authenticated user
    Route::get('/messages/{userId}', 'getMessages');   // Get chat history
    Route::post('/messages', 'sendMessage');           // Send a new message
});

Route::middleware('auth:sanctum')->prefix('sessions')->controller(SessionController::class)->group(function () {
    Route::post('/', 'create');                       // Trainer creates a session
    Route::get('/', 'index');                         // Trainer's upcoming sessions
    Route::get('/client', 'clientSessions');          // Client's upcoming sessions
    Route::put('/{id}', 'update');                    // Update session details
    Route::put('/{id}/status', 'updateStatus');       // Update only session status
    Route::patch('/{id}/cancel', 'cancel');           // Cancel session (change status)
    Route::delete('/{id}', 'destroy');                // Permanently delete session
});

Route::middleware('auth:sanctum')->prefix('tasks')->controller(TaskController::class)->group(function () {
    Route::get('/', 'index');                         // Get all tasks for authenticated user
    Route::post('/', 'store');                        // Create a new task
    Route::get('/statistics', 'statistics');          // Get task statistics
    Route::get('/{task}', 'show');                    // Get specific task
    Route::put('/{task}', 'update');                  // Update task
    Route::delete('/{task}', 'destroy');              // Delete task
    Route::patch('/{task}/complete', 'markCompleted'); // Mark task as completed
});

Route::middleware('auth:sanctum')->prefix('diet-plans')->controller(DietPlanController::class)->group(function () {
    Route::get('/', 'index');                         // Get all diet plans for authenticated trainer
    Route::post('/generate', 'generate');             // Generate a new diet plan using AI
    Route::get('/{id}', 'show');                      // Get specific diet plan
    Route::put('/{id}', 'update');                    // Update diet plan
    Route::delete('/{id}', 'destroy');                // Delete diet plan
});