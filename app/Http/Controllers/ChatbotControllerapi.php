<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserConvJourneydataapi;
use App\Models\submitCallbackPreference;
use App\Models\submitSatisfaction;
use App\Models\terminateChat;
use App\Models\terminateResponse;
use App\Models\terminatTicketsData;
use App\Models\submitDetails;
use App\Models\ChatbotDataapi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;


use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;



class ChatbotControllerapi extends Controller
{
    // public function initRecordingConversation(Request $request)
    // {
    //     $userId = $request->input('user_id');

    //     try {
    //         // Check if conversation journey already exists
    //         $existingConversation = UserConvJourneydataapi::where('user_conv_journey_id', $userId)->first();
    //         if ($existingConversation) {
    //             return response()->json(["message" => "Conversation journey already exists."], 400);
    //         }

    //         DB::beginTransaction();

    //         // Create a new conversation journey record
    //         $newConversation = new UserConvJourneydataapi();
    //         $newConversation->user_conv_journey_id = $userId;
    //         $newConversation->user_conversation = "";
    //         $newConversation->save();

    //         // Create chatbot data and set conversation start time
    //         $chatbotData = new ChatbotDataapi();
    //         $chatbotData->user_id = $userId;
    //         $chatbotData->name = "Unknown";
    //         $chatbotData->conv_started = Carbon::now()->toTimeString();
    //         $chatbotData->user_conv_journey_id = $userId;
    //         $chatbotData->save();

    //         DB::commit();

    //         Log::info("Conversation started at " . $chatbotData->conv_started);

    //         return response()->json(["message" => "Conversation journey initialized."], 200);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error("Database error: " . $e->getMessage());
    //         return response()->json(["message" => "Database error occurred."], 500);
    //     }
    // }

    public function initRecordingConversation(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|string',
            ]);

            $userId = $request->input('user_id');

            // Check if conversation journey already exists
            $existingConversation = UserConvJourneydataapi::where('user_conv_journey_id', $userId)->first();
            if ($existingConversation) {
                return response()->json(["status" => "error", "message" => "Conversation journey already exists."], 400);
            }

            DB::beginTransaction();

            // Create a new conversation journey record
            $newConversation = new UserConvJourneydataapi();
            $newConversation->user_conv_journey_id = $userId; // Ensure ID is correctly set
            $newConversation->user_conversation = "";
            $newConversation->save();

            // Create chatbot data and set conversation start time
            $chatbotData = new ChatbotDataapi();
            $chatbotData->user_id = $userId;
            $chatbotData->name = "Unknown";
            $chatbotData->conv_started = Carbon::now()->toTimeString();
            $chatbotData->user_conv_journey_id = $userId;
            $chatbotData->save();

            DB::commit();

            Log::info("Conversation started for User ID: $userId at " . $chatbotData->conv_started);

            return response()->json([
                "status" => "success",
                "message" => "Conversation journey initialized.",
                "user_conv_journey_id" => $newConversation->user_conv_journey_id
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Database error: " . $e->getMessage());
            return response()->json(["status" => "error", "message" => "Database error occurred."], 500);
        }
    }

    // Store User Conversation Response
    // public function storeUserConversation(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'user_conv_journey_id' => 'required|',
    //             'user_conversation' => 'required|string',
    //         ]);

    //         $journeyId = $request->input('user_conv_journey_id');
    //         $userConversation = $request->input('user_conversation');

    //         // Check if the conversation journey exists
    //         $conversation = UserConvJourneydataapi::where('user_conv_journey_id', $journeyId)->first();
    //         if (!$conversation) {
    //             return response()->json(["status" => "error", "message" => "Conversation journey not found."], 404);
    //         }

    //         // Update the conversation
    //         $conversation->user_conversation = $userConversation;
    //         $conversation->save();

    //         return response()->json([
    //             "status" => "success",
    //             "message" => "User conversation updated successfully.",
    //             "user_conv_journey_id" => $journeyId
    //         ], 200);

    //     } catch (\Exception $e) {
    //         Log::error("Error storing user conversation: " . $e->getMessage());
    //         return response()->json(["status" => "error", "message" => "An error occurred while saving the conversation."], 500);
    //     }
    // }

    

    public function submitSatisfaction(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'user_id' => 'required|string|max:255',
            'message' => 'required|numeric|between:0,5'
        ], [
            'user_id.required' => 'User ID is required.',
            'message.required' => 'Satisfaction level is required.',
            'message.numeric' => 'Satisfaction level must be a number.',
            'message.between' => 'Satisfaction level should be between 0 and 5.'
        ]);

        $userId = $validatedData['user_id'];
        $satisfactionLevel = $validatedData['message'];

        // Retrieve the user data from the database
        $user = submitSatisfaction::where('user_id', $userId)->first();

        if (!$user) {
            Log::warning("User {$userId} not found.");
            return response()->json(["message" => "User not found. Please start a new session."], 404);
        }

        try {
            // Update the user's satisfaction level
            $user->satisfaction_level = $satisfactionLevel;
            $user->save();

            // Optionally, append to conversation logs if such a method exists
            // $this->appendToConversation($userId, "User", $satisfactionLevel);
            // $this->appendToConversation($userId, "Chatbot", "Thank you for your feedback!");

            Log::info("User {$userId} provided satisfaction level: {$satisfactionLevel}");

            return response()->json(["message" => "Thank you for your feedback!"], 200);
        } catch (\Exception $e) {
            Log::error("An error occurred while saving satisfaction level for user {$userId}: {$e->getMessage()}");
            return response()->json(["message" => "An error occurred. Please try again."], 500);
        }
    }

    // Example method to append to conversation logs
    // protected function appendToConversation($userId, $sender, $message)
    // {
    //     // Implement your conversation logging logic here
    // }

    public function terminateChat(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'user_id' => 'required|string|max:255',
        ], [
            'user_id.required' => 'User ID is required.',
        ]);

        $userId = $validatedData['user_id'];

        // Retrieve the user data from the database
        $user = terminateChat::where('user_id', $userId)->first();

        if ($user) {
            Log::info("User {$userId} requested to terminate the chat at level {$user->session_level}");

            // Append to conversation logs if such a method exists
            $this->appendToConversation($userId, "User", "I want to end this conversation");
            $this->appendToConversation($userId, "Chatbot", "Why are you leaving so soon? Tell our representatives how we can be of help. (Yes/No)");

            return response()->json([
                "message" => "Why are you leaving so soon? Tell our representatives how we can be of help. (Yes/No)"
            ], 200);
        } else {
            Log::warning("User {$userId} not found.");
            return response()->json([
                "message" => "User not found. Please start a new session."
            ], 404);
        }
    }

    // Example method to append to conversation logs
   
    // public function terminateResponse(Request $request)
    // {
    //     // ✅ Step 1: Validate Input
    //     $validator = Validator::make($request->all(), [
    //         'user_id' => 'required|string|exists:atjoin_chatbot_data,user_id',
    //         'message' => 'required|string'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             "status" => "error",
    //             "message" => "Invalid input data",
    //             "errors" => $validator->errors()
    //         ], 422);
    //     }

    //     $userId = $request->input('user_id');
    //     $response = strtoupper($request->input('message'));

    //     // ✅ Step 2: Fetch user and conversation journey data
    //     $userData = TerminateResponse::where('user_id', $userId)->first();
    //     $convJourney = UserConvJourneyDataApi::where('user_conv_journey_id', $userId)->first();

    //     if (!$userData) {
    //         return response()->json([
    //             "status" => "error",
    //             "message" => "User data not found for ID: $userId"
    //         ], 404);
    //     }

    //     if (!$convJourney) {
    //         return response()->json([
    //             "status" => "error",
    //             "message" => "Conversation journey not found for user ID: $userId"
    //         ], 404);
    //     }

    //     // ✅ Step 3: Handle "YES" Response
    //     if ($response === "YES") {
    //         $this->appendToConversation($userId, "User", $response);
    //         $apiResponse = "Please wait while we reconnect you...";
    //         $this->appendToConversation($userId, "Chatbot", $apiResponse);
    //         return response()->json(["message" => $apiResponse], 200);
    //     }

    //     // ✅ Step 4: Handle "NO" Response
    //     if ($response === "NO") {
    //         $this->appendToConversation($userId, "User", $response);

    //         DB::beginTransaction();
    //         try {
    //             if ($userData->callback_requested || $userData->userquery) {
    //                 // Create new ticket
    //                 $newTicket = new terminatTicketsData([
    //                     'ticket_id' => $userId,
    //                     'user_id' => $userId,
    //                     'user_name' => $userData->name,
    //                     'contact' => $userData->contact,
    //                     'email' => $userData->email,
    //                     'callback_requested' => $userData->callback_requested,
    //                     'userquery' => $userData->userquery,
    //                     'user_conv_journey_id' => $userId,
    //                     'is_ticket_resolved' => false,
    //                     'ticket_starred' => false,
    //                     'ticket_resolution_status' => 'Pending'
    //                 ]);
    //                 $newTicket->save();
    //             }

    //             // Mark session as terminated
    //             $userData->is_terminated = true;
    //             $userData->conv_ended = Carbon::now();
    //             $userData->save();

    //             // Append conversation log
    //             $this->appendToConversation($userId, "Chatbot", "Thank you for using our service. Have a great day!");

    //             // Send email
    //             $emailSent = $this->sendConversationEmail($userData, $convJourney);

    //             DB::commit();

    //             $responseData = [
    //                 "message" => "Thank you for using our service. Have a great day!",
    //                 "ticket_id" => $userId
    //             ];

    //             if (!$emailSent) {
    //                 $responseData["email_status"] = "Failed to send email.";
    //             }

    //             return response()->json($responseData, 200);

    //         } catch (\Exception $e) {
    //             DB::rollBack();
    //             Log::error("❌ ERROR: Failed to process termination for user $userId: " . $e->getMessage());

    //             return response()->json([
    //                 "status" => "error",
    //                 "message" => "An internal error occurred. Please try again.",
    //                 "debug_info" => $e->getMessage() // Use this only for debugging, remove in production!
    //             ], 500);
    //         }
    //     }

    //     return response()->json([
    //         "status" => "error",
    //         "message" => "Invalid response. Please send 'Yes' or 'No'."
    //     ], 400);
    // }

    // private function appendToConversation($userId, $sender, $message)
    // {
    //     Log::info("[$sender] User {$userId}: {$message}");
    // }

    // private function sendConversationEmail($userData, $convJourney)
    // {
    //     // Placeholder for email sending logic
    //     return true;
    // }

    public function terminateResponse(Request $request)
{
    // ✅ Step 1: Validate Input
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|string|exists:atjoin_chatbot_data,user_id',
        'message' => 'required|string'
    ]);

    if ($validator->fails()) {
        return response()->json([
            "status" => "error",
            "message" => "Invalid input data",
            "errors" => $validator->errors()
        ], 422);
    }

    $userId = $request->input('user_id');
    $response = strtoupper($request->input('message'));

    // ✅ Step 2: Fetch user and conversation journey data
    $userData = TerminateResponse::where('user_id', $userId)->first();
    $convJourney = UserConvJourneyDataApi::where('user_conv_journey_id', $userId)->first();

    if (!$userData) {
        return response()->json([
            "status" => "error",
            "message" => "User data not found for ID: $userId"
        ], 404);
    }

    if (!$convJourney) {
        return response()->json([
            "status" => "error",
            "message" => "Conversation journey not found for user ID: $userId"
        ], 404);
    }

    // ✅ Step 3: Handle "YES" Response
    if ($response === "YES") {
        $this->appendToConversation($userId, "User", $response);
        $apiResponse = "Please wait while we reconnect you...";
        $this->appendToConversation($userId, "Chatbot", $apiResponse);
        return response()->json(["message" => $apiResponse], 200);
    }

    // ✅ Step 4: Handle "NO" Response
    if ($response === "NO") {
        $this->appendToConversation($userId, "User", $response);

        DB::beginTransaction();
        try {
            if ($userData->callback_requested || $userData->userquery) {
                // Determine ticket title based on user query
                $ticketTitle = $userData->userquery 
                    ? "Received a query by {$userData->name} regarding {$userData->userquery}"
                    : "Callback requested by {$userData->name}";

                // Create new ticket
                $newTicket = new terminatTicketsData([
                    'ticket_id' => $userId,
                    'user_id' => $userId,
                    'user_name' => $userData->name,
                    'contact' => $userData->contact,
                    'email' => $userData->email,
                    'callback_requested' => $userData->callback_requested,
                    'userquery' => $userData->userquery,
                    'user_conv_journey_id' => $userId,
                    'is_ticket_resolved' => false,
                    'ticket_starred' => false,
                    'ticket_resolution_status' => 'Pending',
                    'ticket_title' => $ticketTitle,
                    'ticket_created' => Carbon::now('Asia/Kolkata') // ⏰ IST Time Added
                ]);
                $newTicket->save();
            }

            // Mark session as terminated
            $userData->is_terminated = true;
            $userData->conv_ended = Carbon::now();
            $userData->save();

            // Append conversation log
            $this->appendToConversation($userId, "Chatbot", "Thank you for using our service. Have a great day!");

            // Send email
            $emailSent = $this->sendConversationEmail($userData, $convJourney);

            DB::commit();

            $responseData = [
                "message" => "Thank you for using our service. Have a great day!",
                "ticket_id" => $userId
            ];

            if (!$emailSent) {
                $responseData["email_status"] = "Failed to send email.";
            }

            return response()->json($responseData, 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ ERROR: Failed to process termination for user $userId: " . $e->getMessage());

            return response()->json([
                "status" => "error",
                "message" => "An internal error occurred. Please try again.",
                "debug_info" => $e->getMessage() // Use this only for debugging, remove in production!
            ], 500);
        }
    }

    return response()->json([
        "status" => "error",
        "message" => "Invalid response. Please send 'Yes' or 'No'."
    ], 400);
}

private function appendToConversation($userId, $sender, $message)
{
    Log::info("[$sender] User {$userId}: {$message}");
}

private function sendConversationEmail($userData, $convJourney)
{
    // Placeholder for email sending logic
    return true;
}


//     public function terminateResponse(Request $request)
// {
//     // ✅ Step 1: Validate Input
//     $validator = Validator::make($request->all(), [
//         'user_id' => 'required|string|exists:atjoin_chatbot_data,user_id',
//         'message' => 'required|string'
//     ]);

//     if ($validator->fails()) {
//         return response()->json([
//             "status" => "error",
//             "message" => "Invalid input data",
//             "errors" => $validator->errors()
//         ], 422);
//     }

//     $userId = $request->input('user_id');
//     $response = strtoupper($request->input('message'));

//     // ✅ Step 2: Fetch user and conversation journey data
//     $userData = TerminateResponse::where('user_id', $userId)->first();
//     $convJourney = UserConvJourneyDataApi::where('user_conv_journey_id', $userId)->first();

//     if (!$userData) {
//         return response()->json([
//             "status" => "error",
//             "message" => "User data not found for ID: $userId"
//         ], 404);
//     }

//     if (!$convJourney) {
//         return response()->json([
//             "status" => "error",
//             "message" => "Conversation journey not found for user ID: $userId"
//         ], 404);
//     }

//     // ✅ Step 3: Handle "YES" Response
//     if ($response === "YES") {
//         $this->appendToConversation($userId, "User", $response);
//         $apiResponse = "Please wait while we reconnect you...";
//         $this->appendToConversation($userId, "Chatbot", $apiResponse);
//         return response()->json(["message" => $apiResponse], 200);
//     }

//     // ✅ Step 4: Handle "NO" Response
//     if ($response === "NO") {
//         $this->appendToConversation($userId, "User", $response);

//         DB::beginTransaction();
//         try {
//             if ($userData->callback_requested || $userData->userquery) {
//                 // Determine ticket title based on user query
//                 $ticketTitle = $userData->userquery 
//                     ? "Received a query by {$userData->name} regarding {$userData->userquery}"
//                     : "Callback requested by {$userData->name}";

//                 // Create new ticket
//                 $newTicket = new terminatTicketsData([
//                     'ticket_id' => $userId,
//                     'user_id' => $userId,
//                     'user_name' => $userData->name,
//                     'contact' => $userData->contact,
//                     'email' => $userData->email,
//                     'callback_requested' => $userData->callback_requested,
//                     'userquery' => $userData->userquery,
//                     'user_conv_journey_id' => $userId,
//                     'is_ticket_resolved' => false,
//                     'ticket_starred' => false,
//                     'ticket_resolution_status' => 'Pending',
//                     'ticket_title' => $ticketTitle  // Added the new field
//                 ]);
//                 $newTicket->save();
//             }

//             // Mark session as terminated
//             $userData->is_terminated = true;
//             $userData->conv_ended = Carbon::now();
//             $userData->save();

//             // Append conversation log
//             $this->appendToConversation($userId, "Chatbot", "Thank you for using our service. Have a great day!");

//             // Send email
//             $emailSent = $this->sendConversationEmail($userData, $convJourney);

//             DB::commit();

//             $responseData = [
//                 "message" => "Thank you for using our service. Have a great day!",
//                 "ticket_id" => $userId
//             ];

//             if (!$emailSent) {
//                 $responseData["email_status"] = "Failed to send email.";
//             }

//             return response()->json($responseData, 200);

//         } catch (\Exception $e) {
//             DB::rollBack();
//             Log::error("❌ ERROR: Failed to process termination for user $userId: " . $e->getMessage());

//             return response()->json([
//                 "status" => "error",
//                 "message" => "An internal error occurred. Please try again.",
//                 "debug_info" => $e->getMessage() // Use this only for debugging, remove in production!
//             ], 500);
//         }
//     }

//     return response()->json([
//         "status" => "error",
//         "message" => "Invalid response. Please send 'Yes' or 'No'."
//     ], 400);
// }

// private function appendToConversation($userId, $sender, $message)
// {
//     Log::info("[$sender] User {$userId}: {$message}");
// }

// private function sendConversationEmail($userData, $convJourney)
// {
//     // Placeholder for email sending logic
//     return true;
// }
    public function submitCallbackPreference(Request $request)
    {
        $userId = $request->input('user_id');
        $userResponse = $request->input('message');
    
        // Fetch user data
        $userData = submitCallbackPreference::where('user_id', $userId)->first();
    
        if (!$userData) {
            Log::error("User {$userId} not found");
            return response()->json(["message" => "User not found. Please start a new session."], 404);
        }
    
        if ($userResponse) {
            try {
                DB::beginTransaction();
    
                $responseMessage = "";
    
                if ($userResponse === "Yes") {
                    $userData->callback_requested = true;
                    $responseMessage = "Our representatives will reach out to you. Please give us details.";
                } elseif ($userResponse === "No") {
                    $userData->callback_requested = false;
                    $responseMessage = "Thank you for using our services.";
                }
    
                $userData->session_level = 7;
                $userData->save();
    
                // Append conversation log
                $this->appendToConversation($userId, "User", $userResponse);
                $this->appendToConversation($userId, "Chatbot", $responseMessage);
    
                DB::commit();
                Log::info("User {$userId} provided callback preference and moved to level 7");
    
                return response()->json(["message" => $responseMessage]);
    
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("An error occurred: " . $e->getMessage());
                return response()->json(["message" => "An error occurred. Please try again."], 500);
            }
        }
    
        return response()->json(["message" => "Invalid request."], 400);
    }
    
    // private function appendToConversation($userId, $sender, $message)
    // {
    //     // Assuming there's a conversation logging mechanism
    //     Log::info("[$sender] User {$userId}: {$message}");
    // }

    public function submitDetails(Request $request)
    {
        $userId = $request->input('user_id');
        $userResponse = $request->input('message');
        $userQuery = $request->input('user_query', null); // Optional

        // Find user
        $userData = submitDetails::where('user_id', $userId)->first();
        if (!$userData) {
            Log::error("User {$userId} not found");
            return response()->json(["message" => "User not found. Please start a new session."], 404);
        }

        if ($userResponse) {
            try {
                DB::beginTransaction();

                // Split the response
                $details = explode(',', $userResponse);
                if (count($details) < 3) {
                    throw new \Exception("Invalid format");
                }

                $userData->name = trim($details[0]);
                $userData->contact = isset($details[1]) ? trim($details[1]) : '';
                $userData->email = isset($details[2]) ? trim($details[2]) : '';
                $userData->session_level = 6;

                // Log conversation
                $this->appendToConversation($userId, "User", "Details provided: {$userResponse}");
                Log::info("User {$userId} provided details and moved to level 6");

                // Determine response message
                if ($userQuery) {
                    $userData->userquery = trim($userQuery);
                    $this->appendToConversation($userId, "User", "Query: {$userQuery}");
                    Log::info("User {$userId} query saved: {$userQuery}");
                    $message = "Your details have been saved and your query has been registered. Please give us a rating:";
                } else {
                    $message = "Our representatives will reach out to you. Please give us a rating:";
                }

                $userData->save();
                DB::commit();

                $this->appendToConversation($userId, "Chatbot", $message);

                return response()->json([
                    "message" => $message
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Error processing user details: " . $e->getMessage());
                return response()->json([
                    "message" => "Please provide your details in the format: name, contact, email"
                ], 400);
            }
        }

        return response()->json(["message" => "No response provided."], 400);
    }

    private function logConversation($userId, $sender, $message)
{
    Log::info("[$sender] User {$userId}: {$message}");
}


public function getQuestionChain(Request $request)
{
    try {
        $request->validate([
            'p_question_parent_level' => 'required|integer',
            'p_client_id' => 'required|integer',
        ]);

        $p_question_parent_level = $request->p_question_parent_level;
        $p_client_id = $request->p_client_id;

        $questions = DB::select("
            WITH RECURSIVE question_chain AS (
                SELECT question_level, question_text, question_parent_level, client_id
                FROM questions
                WHERE question_level = ? AND client_id = ?

                UNION ALL

                SELECT e.question_level, e.question_text, e.question_parent_level, e.client_id
                FROM questions e
                JOIN question_chain ec ON e.question_level = ec.question_parent_level AND e.client_id = ec.client_id
            )
            SELECT * FROM question_chain ORDER BY question_parent_level ASC;
        ", [$p_question_parent_level, $p_client_id]);

        if (empty($questions)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No questions found for the given parent level and client ID.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Question chain retrieved successfully.',
            'data' => $questions
        ], 200);

    } catch (\Exception $e) {
        Log::error('Error fetching question chain: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred while retrieving the question chain.',
        ], 500);
    }
}

// public function getQuestionChain(Request $request)
// {
//     try {
//         $request->validate([
//             'p_question_level' => 'required|integer', // The specific question level to retrieve
//             'p_client_id' => 'required|integer',
//         ]);

//         $p_question_level = $request->p_question_level;
//         $p_client_id = $request->p_client_id;

//         $questions = DB::select("
//             WITH RECURSIVE question_chain AS (
//                 -- Start with the requested question level
//                 SELECT question_level, question_text, question_parent_level, client_id
//                 FROM questions
//                 WHERE question_level = ? AND client_id = ?

//                 UNION ALL

//                 -- Recursively fetch parent questions leading up to the root
//                 SELECT q.question_level, q.question_text, q.question_parent_level, q.client_id
//                 FROM questions q
//                 JOIN question_chain qc ON q.question_level = qc.question_parent_level
//                 WHERE q.client_id = ?
//             )
//             SELECT * FROM question_chain ORDER BY question_level ASC;
//         ", [$p_question_level, $p_client_id, $p_client_id]);

//         if (empty($questions)) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'No questions found for the given level and client ID.',
//             ], 404);
//         }

//         return response()->json([
//             'status' => 'success',
//             'message' => 'Question chain retrieved successfully.',
//             'data' => $questions
//         ], 200);

//     } catch (\Exception $e) {
//         Log::error('Error fetching question chain: ' . $e->getMessage());
//         return response()->json([
//             'status' => 'error',
//             'message' => 'An error occurred while retrieving the question chain.',
//         ], 500);
//     }
// }


// Store User Conversation Response



}