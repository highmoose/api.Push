<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\TrainerController;

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
    Route::get('/', 'clients');
    Route::post('/', 'addClient');
    Route::post('/addTemp', 'addTempClient');
    Route::get('{id}', 'getClient');
    Route::put('{id}', 'updateClient');
    Route::delete('{id}', 'deleteClient');
});

Route::middleware('auth:sanctum')->controller(MessageController::class)->group(function () {
    Route::get('/conversations', 'conversations');     // List all conversations
    Route::get('/messages', 'getAllMessages');
    Route::get('/messages/{userId}', 'getMessages');   // Get chat history
    Route::post('/messages', 'sendMessage');           // Send a new message
});
