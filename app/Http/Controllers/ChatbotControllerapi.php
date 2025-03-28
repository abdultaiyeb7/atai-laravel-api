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


use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;



class ChatbotControllerapi extends Controller
{
    public function initRecordingConversation(Request $request)
    {
        $userId = $request->input('user_id');

        try {
            // Check if conversation journey already exists
            $existingConversation = UserConvJourneydataapi::where('user_conv_journey_id', $userId)->first();
            if ($existingConversation) {
                return response()->json(["message" => "Conversation journey already exists."], 400);
            }

            DB::beginTransaction();

            // Create a new conversation journey record
            $newConversation = new UserConvJourneydataapi();
            $newConversation->user_conv_journey_id = $userId;
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

            Log::info("Conversation started at " . $chatbotData->conv_started);

            return response()->json(["message" => "Conversation journey initialized."], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Database error: " . $e->getMessage());
            return response()->json(["message" => "Database error occurred."], 500);
        }
    }

    // public function submitCallbackPreference(Request $request)
    // {
    //     try {
    //         // Validate request
    //         $validatedData = $request->validate([
    //             'user_id' => 'required|string|max:255',
    //             'message' => 'required|string|in:Yes,No'
    //         ], [
    //             'user_id.required' => 'User ID is required.',
    //             'message.required' => 'Message is required.',
    //             'message.in' => 'Message must be either Yes or No.'
    //         ]);

    //         $userId = $validatedData['user_id'];
    //         $userResponse = $validatedData['message'];

    //         // Fetch user from the database
    //         $user = ChatbotDataapi::where('user_id', $userId)->first();

    //         if (!$user) {
    //             return response()->json(["message" => "User not found. Please start a new session."], 404);
    //         }

    //         if ($userResponse === "Yes") {
    //             // Update callback request
    //             $user->callback_requested = true;
    //             $user->save();

    //             // Return user details along with their actual session level
    //             return response()->json([
    //                 "message" => "Kindly provide your details to help us provide you the best service:",
    //                 "details" => [
    //                     "name" => $user->name,
    //                     "email" => $user->email,
    //                     "contact" => $user->contact,
    //                     "session_level" => $user->session_level // Show actual session level
    //                 ]
    //             ]);
    //         } else {
    //             // If user says "No", just thank them
    //             return response()->json(["message" => "Thank you for visiting us."], 200);
    //         }

    //     } catch (\Exception $e) {
    //         Log::error("Error in submitCallbackPreference: " . $e->getMessage());
    //         return response()->json([
    //             "message" => "An error occurred. Please try again.",
    //             "error" => $e->getMessage()
    //         ], 500);
    //     }
    // }
//     public function submitCallbackPreference_atai(Request $request)
// {
//     try {
//         // Validate request
//         $validatedData = $request->validate([
//             'user_id' => 'required|string|max:255',
//             'message' => 'required|string|in:Yes,No'
//         ], [
//             'user_id.required' => 'User ID is required.',
//             'message.required' => 'Message is required.',
//             'message.in' => 'Message must be either Yes or No.'
//         ]);

//         $user = ChatbotDataapi::where('user_id', $validatedData['user_id'])->first();

//         if (!$user) {
//             return response()->json(["message" => "User not found. Please start a new session."], 404);
//         }

//         if ($validatedData['message'] === "Yes") {
//             // Update callback request
//             $user->update(['callback_requested' => true]);

//             return response()->json([
//                 "message" => "Kindly provide your details to help us serve you better:",
//                 "details" => [
//                     "name" => $user->name,
//                     "email" => $user->email,
//                     "contact" => $user->contact,
//                     "session_level" => $user->session_level
//                 ]
//             ]);
//         }

//         return response()->json(["message" => "Thank you for visiting us."], 200);

//     } catch (\Illuminate\Validation\ValidationException $e) {
//         return response()->json(["message" => $e->getMessage()], 422);
//     } catch (\Exception $e) {
//         Log::error("Error in submitCallbackPreference_atai: " . $e->getMessage());
//         return response()->json([
//             "message" => "An unexpected error occurred. Please try again.",
//         ], 500);
//     }
// }


//     public function submitDetails(Request $request)
//     {
//         $data = $request->json()->all();
//         $userId = $data['user_id'] ?? null;
//         $userResponse = $data['message'] ?? null;
//         $userQuery = $data['user_query'] ?? null;

//         $userData = ChatbotDataapi::where('user_id', $userId)->first();

//         if (!$userData) {
//             Log::info("User $userId not found");
//             return response()->json(["message" => "User not found. Please start a new session."]);
//         }

//         if ($userResponse) {
//             try {
//                 $details = explode(',', $userResponse);
//                 if (count($details) < 3) {
//                     throw new \Exception("Invalid format");
//                 }

//                 $userData->name = trim($details[0]);
//                 $userData->contact = trim($details[1] ?? '');
//                 $userData->email = trim($details[2] ?? '');
//                 $userData->session_level = 6;
//                 $userData->save();

//                 $this->appendToConversation($userId, "User", "Details provided: $userResponse");
//                 Log::info("User $userId provided details and moved to level 6");

//                 if ($userQuery) {
//                     $userData->userquery = trim($userQuery);
//                     $userData->save();
//                     $this->appendToConversation($userId, "User", "Query: $userQuery");

//                     $apiResponse = "Your details have been saved and your query has been registered. Please give us a rating:";
//                     $this->appendToConversation($userId, "Chatbot", $apiResponse);
//                     Log::info("User $userId query saved: $userQuery");

//                     return response()->json(["message" => $apiResponse]); // ✅ Only returning the message
//                 }

//                 $apiResponse = "Our representatives will reach out to you. Please give us a rating:";
//                 $this->appendToConversation($userId, "Chatbot", $apiResponse);
//                 return response()->json(["message" => $apiResponse]); // ✅ Only returning the message

//             } catch (\Exception $e) {
//                 $errorMessage = "Please provide your details in the format: name, contact, email";
//                 $this->appendToConversation($userId, "Chatbot", $errorMessage);
//                 return response()->json(["message" => $errorMessage]);
//             }
//         }

//         return response()->json(["message" => "No response provided"]);
//     }

//     private function appendToConversation($userId, $sender, $message)
//     {
//         // Implement logic to save conversation history
//     }

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
   
    public function terminateResponse(Request $request)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'user_id' => 'required|string|max:255',
            'response' => 'required|string|max:1',
        ]);

        $userId = $validatedData['user_id'];
        $response = strtoupper($validatedData['response']);

        // Retrieve user data and conversation journey from the database
        $userData = terminateResponse::where('user_id', $userId)->first();
        $convJourney = UserConvJourneydataapi::where('user_conv_journey_id', $userId)->first();

        if ($userData && $convJourney) {
            if ($response === 'Y') {
                $this->appendToConversation($userId, 'User', $response);
                $this->appendToConversation($userId, 'Chatbot', 'Please wait while we reconnect you...');
                return response()->json([
                    'message' => 'Please wait while we reconnect you...'
                ]);
            } elseif ($response === 'N') {
                $this->appendToConversation($userId, 'User', $response);

                if ($userData->callback_requested || $userData->userquery) {
                    // Create a new ticket
                    $newTicket = terminatTicketsData::create([
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
                    ]);

                    // Mark the conversation as terminated
                    $userData->is_terminated = true;
                    $userData->save();

                    $this->appendToConversation($userId, 'Chatbot', 'Thank you for using our service. Have a great day!');

                    // Send an email with the conversation details
                    // $this->sendConversationEmail($userData, $convJourney);

                    return response()->json([
                        'message' => 'Thank you for using our service. Have a great day!',
                        'ticket_id' => $userId
                    ]);
                } else {
                    $this->appendToConversation($userId, 'Chatbot', 'Thank you for using our service. Have a great day!');
                    $userData->is_terminated = true;
                    $userData->save();
                    return response()->json([
                        'message' => 'Thank you for using our service. Have a great day!'
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Invalid response or no session to continue.'
        ], 400);
    }

   
        protected function cleanConversation($conversation)
    {
        // Implement your conversation cleaning logic here
        return $conversation;
    }


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

                if ($userResponse === "Yes") {
                    $userData->callback_requested = true;
                } elseif ($userResponse === "No") {
                    $userData->callback_requested = false;
                }

                $userData->session_level = 7;
                $userData->save();

                // Append conversation log
                $this->appendToConversation($userId, "User", $userResponse);
                $this->appendToConversation($userId, "Chatbot", "Our representatives will reach out to you. Please give us a rating:");

                DB::commit();
                Log::info("User {$userId} provided callback preference and moved to level 7");

                return response()->json(["message" => "Our representatives will reach out to you. Please give us a rating:"]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("An error occurred: " . $e->getMessage());
                return response()->json(["message" => "An error occurred. Please try again."], 500);
            }
        }

        return response()->json(["message" => "Invalid request."], 400);
    }

    private function appendToConversation($userId, $sender, $message)
    {
        // Assuming there's a conversation logging mechanism
        Log::info("[$sender] User {$userId}: {$message}");
    }

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

}