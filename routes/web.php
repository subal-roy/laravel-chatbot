<?php

use App\Http\Controllers\ChatbotController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/chat', function () {
    return view('chat');
})->name('chat.index');
Route::post('/chat', [ChatbotController::class, 'chat']);
