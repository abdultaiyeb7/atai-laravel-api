<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\QuestionController;

use App\Http\Controllers\UserController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/manage-question', [QuestionController::class, 'manageQuestion']);

Route::get('/get-questions', [QuestionController::class, 'getQuestions']); // New GET route


 Route::put('/update-question', [QuestionController::class, 'updateQuestion']);

Route::delete('/delete-question', [QuestionController::class, 'deleteQuestion']);


Route::post('/check-client', [QuestionController::class, 'checkClientExists']);
Route::get('/get-questions-by-client', [QuestionController::class, 'getQuestionsByClient']);


Route::post('/manage-user', [UserController::class, 'manageUser']);


Route::put('/manage-user', [UserController::class, 'updateUser']);


Route::delete('/manage-user', [UserController::class, 'deleteUser']);


// Route::get('/manage-user', [UserController::class, 'getUser']);
