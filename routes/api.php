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
    Route::prefix('chat/user')->group(function() {
        Route::POST('/{me}', [ApiChatController::class, 'user']);
        Route::POST('/follow/{uid}', [ApiChatController::class, 'follow']);
        Route::POST('/{uid}/note/{note?}', [ApiChatController::class, 'note']);
        Route::POST('/{me}/refresh_user', [ApiChatController::class, 'refreshUser']);
        Route::POST('/{me}/refresh_chat', [ApiChatController::class, 'refreshChats']);
        Route::POST('/search/{keyword}/{all?}', [ApiChatController::class, 'search']);
        Route::POST('/{me}/all', [ApiChatController::class, 'all']);
        Route::POST('/{me}/album', [ApiChatController::class, 'album']);
    });
});
