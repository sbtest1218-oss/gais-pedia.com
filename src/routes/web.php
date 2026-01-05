<?php

use App\Http\Controllers\QuestionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('chatbot');
});

Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
