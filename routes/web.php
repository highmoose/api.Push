<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;

Route::middleware('web')->group(function () {
    Route::post('/login', [LoginController::class, 'store']);
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/debug-csrf', function (\Illuminate\Http\Request $request) {
        return response()->json([
            'XSRF-TOKEN Cookie' => $request->cookie('XSRF-TOKEN'),
            'X-XSRF-TOKEN Header' => $request->header('X-XSRF-TOKEN'),
            'csrf_token() helper' => csrf_token(),
            '_token param' => $request->input('_token'),
        ]);
    });
});
