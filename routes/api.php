<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\QuestionController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/manage-question', [QuestionController::class, 'manageQuestion']);

Route::get('/get-questions', [QuestionController::class, 'getQuestions']); // New GET route


// Route::put('/update-question', [QuestionController::class, 'updateQuestion']);

Route::delete('/delete-question', [QuestionController::class, 'deleteQuestion']);
