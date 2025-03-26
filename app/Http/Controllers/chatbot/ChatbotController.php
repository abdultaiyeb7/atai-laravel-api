<?php

namespace App\Http\Controllers\chatbot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatbotAI\UserConvJourney;
use App\Models\ChatbotAI\ChatbotData;


use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;



class ChatbotController extends Controller
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
            $existingUser = UserConvJourney::where('user_conv_journey_id', $userId)->first();
            
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

    public function submitCallbackPreference(Request $request)
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

            $userId = $validatedData['user_id'];
            $userResponse = $validatedData['message'];

            // Fetch user from the database
            $user = ChatbotData::where('user_id', $userId)->first();

            if (!$user) {
                return response()->json(["message" => "User not found. Please start a new session."], 404);
            }

            if ($userResponse === "Yes") {
                // Update callback request
                $user->callback_requested = true;
                $user->save();

                // Return user details along with their actual session level
                return response()->json([
                    "message" => "Kindly provide your details to help us provide you the best service:",
                    "details" => [
                        "name" => $user->name,
                        "email" => $user->email,
                        "contact" => $user->contact,
                        "session_level" => $user->session_level // Show actual session level
                    ]
                ]);
            } else {
                // If user says "No", just thank them
                return response()->json(["message" => "Thank you for visiting us."], 200);
            }

        } catch (\Exception $e) {
            Log::error("Error in submitCallbackPreference: " . $e->getMessage());
            return response()->json([
                "message" => "An error occurred. Please try again.",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}