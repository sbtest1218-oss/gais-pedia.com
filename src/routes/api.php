<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\QuestionController;
use Illuminate\Support\Facades\Route;

Route::post('/chat', [ChatController::class, 'chat']);

Route::post('/questions', [QuestionController::class, 'store']);
Route::get('/questions', [QuestionController::class, 'list']);
Route::post('/questions/{question}/helpful', [QuestionController::class, 'helpful']);
