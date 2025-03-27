<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\QuestionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TicketController;

use App\Http\Controllers\ChatbotControllerapi;



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
 Route::get('/manage-user', [UserController::class, 'getUser']);



 Route::get('/tickets/get_all_tickets_info', [TicketController::class, 'getAllTicketsInfo']);

 Route::get('/tickets/get_user_conversation', [TicketController::class, 'getUserConversation']);

 Route::get('/tickets/get_ticket_userInfo', [TicketController::class, 'getTicketUserInfo']);

 Route::get('/tickets/all_callback_requests', [TicketController::class, 'getAllCallbackRequests']);


 Route::get('/tickets/total_ticket_count', [TicketController::class, 'getTotalTicketCount']);


 Route::post('/tickets/star_ticket', [TicketController::class, 'starTicket']);


 Route::post('/tickets/un_star_ticket', [TicketController::class, 'unStarTicket']);


 Route::get('/tickets/starred_ticket_count', [TicketController::class, 'getStarredTicketCount']);

 Route::post('/tickets/userquery_resolution_status', [TicketController::class, 'updateUserQueryResolutionStatus']);

 Route::post('/tickets/callback_request_resolution_status', [TicketController::class, 'updateCallbackRequestResolutionStatus']);

 Route::post('/tickets/resolve_ticket', [TicketController::class, 'resolveTicket']);

 Route::get('/tickets/resolved_ticket_count', [TicketController::class, 'getResolvedTicketCount']);

 Route::get('/tickets/unresolved_ticket_count', [TicketController::class, 'getUnresolvedTicketCount']);


 Route::get('/tickets/ticket_resolved_time', [TicketController::class, 'getTicketResolvedTime']);


 Route::get('/tickets/conversation_duration', [TicketController::class, 'getConversationDuration']);


 Route::post('/tickets/save_remark_and_followup', [TicketController::class, 'saveRemarkAndFollowUp']);

// Route::get('/tickets/get_remarks', [TicketController::class, 'getRemarks']);
Route::get('/tickets/get_remarks', [TicketController::class, 'getRemarks']);


 Route::get('/tickets/follow_up_tickets', [TicketController::class, 'getFollowUpTickets']);

 Route::post('/tickets/ticket_resolution_status', [TicketController::class, 'updateTicketResolutionStatus']);


 Route::get('/status/get_descriptions', [TicketController::class, 'getAllStatus']);





 Route::post('/chatbot/init_recording_conversation/', [ChatbotControllerapi::class, 'initRecordingConversation']);


 Route::post('/chatbot/submit_callback_preference', [ChatbotControllerapi::class, 'submitCallbackPreference']);


 Route::post('/chatbot/submit_details', [ChatbotControllerapi::class, 'submitDetails']);




 
 Route::get('/tickets/get_all_tickets_info_api', [TicketController::class, 'getAllTicketsInfoapi']);