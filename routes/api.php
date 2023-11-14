<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiChatController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth.simple')->group(function() {
    Route::POST('/chat/user/{me}', [ApiChatController::class, 'user']);
    Route::POST('/chat/user/follow/{uid}', [ApiChatController::class, 'follow']);
    Route::POST('/chat/user/{uid}/note/{note?}', [ApiChatController::class, 'note']);
    Route::POST('/chat/user/{me}/refresh', [ApiChatController::class, 'refresh']);
});
