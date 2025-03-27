<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserConvJourneydataapi;
use App\Models\ChatbotDataapi;
use App\Models\submitSatisfaction;

use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;



class ChatbotControllerapi extends Controller
{
    public function initRecordingConversation(Request $request)
    {
        try {
            // Validate the request input
            $validatedData = $request->validate([
                'user_id' => 'required|regex:/^[a-zA-Z0-9]+$/|max:255'
            ], [
                'user_id.required' => 'The user_id field is required.',
                'user_id.regex' => 'The user_id must contain only letters and numbers.',  
                'user_id.max' => 'The user_id is too long.'
            ]);

            $userId = $validatedData['user_id'];

            Log::info("Received user_id: " . $userId); // Debugging log

            // Check if the conversation journey exists
            $existingUser = UserConvJourneydataapi::where('user_conv_journey_id', $userId)->first();
            
            if (!$existingUser) {
                // Manually insert the data and check if it's inserted
                $insert = DB::table('user_conv_journey')->insert([
                    'user_conv_journey_id' => $userId,
                    'user_conversation' => ''
                ]);

                if ($insert) {
                    Log::info("New user inserted successfully: " . $userId);
                    return response()->json(["message" => "Conversation journey initialized."], 200);
                } else {
                    Log::error("Failed to insert new user: " . $userId);
                    return response()->json(["message" => "Failed to insert new user."], 500);
                }
            } else {
                Log::info("User already exists: " . $userId);
                return response()->json(["message" => "Conversation journey already exists."], 400);
            }

        } catch (QueryException $e) {
            Log::error("Database error: " . $e->getMessage());
            return response()->json([
                "message" => "Database error occurred.",
                "error" => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            Log::error("Unexpected error: " . $e->getMessage());
            return response()->json([
                "message" => "An unexpected error occurred.",
                "error" => $e->getMessage()
            ], 500);
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
    public function submitCallbackPreference_atai(Request $request)
{
    try {
        // Validate request
        $validatedData = $request->validate([
            'user_id' => 'required|string|max:255',
            'message' => 'required|string|in:Yes,No'
        ], [
            'user_id.required' => 'User ID is required.',
            'message.required' => 'Message is required.',
            'message.in' => 'Message must be either Yes or No.'
        ]);

        $user = ChatbotDataapi::where('user_id', $validatedData['user_id'])->first();

        if (!$user) {
            return response()->json(["message" => "User not found. Please start a new session."], 404);
        }

        if ($validatedData['message'] === "Yes") {
            // Update callback request
            $user->update(['callback_requested' => true]);

            return response()->json([
                "message" => "Kindly provide your details to help us serve you better:",
                "details" => [
                    "name" => $user->name,
                    "email" => $user->email,
                    "contact" => $user->contact,
                    "session_level" => $user->session_level
                ]
            ]);
        }

        return response()->json(["message" => "Thank you for visiting us."], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(["message" => $e->getMessage()], 422);
    } catch (\Exception $e) {
        Log::error("Error in submitCallbackPreference_atai: " . $e->getMessage());
        return response()->json([
            "message" => "An unexpected error occurred. Please try again.",
        ], 500);
    }
}


    public function submitDetails(Request $request)
    {
        $data = $request->json()->all();
        $userId = $data['user_id'] ?? null;
        $userResponse = $data['message'] ?? null;
        $userQuery = $data['user_query'] ?? null;

        $userData = ChatbotDataapi::where('user_id', $userId)->first();

        if (!$userData) {
            Log::info("User $userId not found");
            return response()->json(["message" => "User not found. Please start a new session."]);
        }

        if ($userResponse) {
            try {
                $details = explode(',', $userResponse);
                if (count($details) < 3) {
                    throw new \Exception("Invalid format");
                }

                $userData->name = trim($details[0]);
                $userData->contact = trim($details[1] ?? '');
                $userData->email = trim($details[2] ?? '');
                $userData->session_level = 6;
                $userData->save();

                $this->appendToConversation($userId, "User", "Details provided: $userResponse");
                Log::info("User $userId provided details and moved to level 6");

                if ($userQuery) {
                    $userData->userquery = trim($userQuery);
                    $userData->save();
                    $this->appendToConversation($userId, "User", "Query: $userQuery");

                    $apiResponse = "Your details have been saved and your query has been registered. Please give us a rating:";
                    $this->appendToConversation($userId, "Chatbot", $apiResponse);
                    Log::info("User $userId query saved: $userQuery");

                    return response()->json(["message" => $apiResponse]); // ✅ Only returning the message
                }

                $apiResponse = "Our representatives will reach out to you. Please give us a rating:";
                $this->appendToConversation($userId, "Chatbot", $apiResponse);
                return response()->json(["message" => $apiResponse]); // ✅ Only returning the message

            } catch (\Exception $e) {
                $errorMessage = "Please provide your details in the format: name, contact, email";
                $this->appendToConversation($userId, "Chatbot", $errorMessage);
                return response()->json(["message" => $errorMessage]);
            }
        }

        return response()->json(["message" => "No response provided"]);
    }

    private function appendToConversation($userId, $sender, $message)
    {
        // Implement logic to save conversation history
    }

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
}