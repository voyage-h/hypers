<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
//    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/', [ChatController::class, 'index']);
    Route::get('/chat/user/{me}', [ChatController::class, 'user']);
    Route::get('/chat/index/refresh', [ChatController::class, 'refresh']);
    Route::get('/chat/{me}/all', [ChatController::class, 'all']);
    Route::get('/chat/{me}/album', [ChatController::class, 'album']);
    Route::get('/chat/{me}/my_album', [ChatController::class, 'myAlbum']);
    Route::get('/chat/{me}/{target}', [ChatController::class, 'detail']);
});

require __DIR__.'/auth.php';
