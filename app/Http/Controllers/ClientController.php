<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClientController extends Controller
{
    // public function insertClient(Request $request)
    // {
    //     try {
    //         // Validate input
    //         $validated = $request->validate([
    //             'client_name' => 'required|string|max:300',
    //             'client_gstin' => 'nullable|string|max:50',
    //             'client_email' => 'required|email|max:300',
    //             'client_contact_number' => 'required|string|max:50',
    //             'client_address' => 'nullable|string|max:1500',
    //             'client_description' => 'nullable|string|max:1500',
    //             'client_no_of_emp' => 'nullable|integer',
    //             'client_profile_photo' => 'nullable|string|max:2000',
    //         ]);

    //         // Set default OUT parameters
    //         DB::statement("SET @client_id = 0;");
    //         DB::statement("SET @message = '';");

    //         // Call the stored procedure
    //         DB::select("CALL manage_clients(
    //             :action_type,
    //             @client_id,
    //             :client_name,
    //             :client_gstin,
    //             :client_email,
    //             :client_contact_number,
    //             :client_address,
    //             :client_description,
    //             :client_no_of_emp,
    //             :client_profile_photo,
    //             @message,
    //             NULL,
    //             NULL
    //         )", [
    //             'action_type' => 'I',
    //             'client_name' => $validated['client_name'],
    //             'client_gstin' => $validated['client_gstin'] ?? '',
    //             'client_email' => $validated['client_email'],
    //             'client_contact_number' => $validated['client_contact_number'],
    //             'client_address' => $validated['client_address'] ?? '',
    //             'client_description' => $validated['client_description'] ?? '',
    //             'client_no_of_emp' => $validated['client_no_of_emp'] ?? 0,
    //             'client_profile_photo' => $validated['client_profile_photo'] ?? '',
    //         ]);

    //         // Get client_id
    //         $results = DB::select('SELECT @client_id as client_id');
    //         $clientId = $results[0]->client_id ?? null;

    //         if ($clientId) {
    //             // Generate verification token
    //             $token = Str::uuid()->toString();

    //             // Insert into users table
    //             DB::table('users')->insert([
    //                 'user_name'     => $validated['client_name'],
    //                 'email'         => $validated['client_email'],
    //                 'mobile'        => $validated['client_contact_number'],
    //                 'profile_pic'   => $validated['client_profile_photo'] ?? '',
    //                 'status'        => 1,
    //                 'token'         => $token,
    //                 'otp'           => null,
    //                 'is_verified'   => 0,
    //                 'is_available'  => 1,
    //                 'created_at'    => Carbon::now(),
    //                 'updated_at'    => Carbon::now(),
    //                 'ClientId'      => $clientId,
    //                 'abbreviation'  => 'A',
    //                 'PANNumber'     => null,
    //                 'DocPath'       => null,
    //             ]);

    //             // Compose verification link
    //             $setupLink = "https://dev.atai.admin.raghavsolars.com/setup-password/?token=" . $token;

    //             // Send verification email
    //             Mail::raw("Hi {$validated['client_name']},\n\nWelcome to ATAI! Please set up your password using the link below:\n\n$setupLink\n\nThanks,\nATAI Team", function ($message) use ($validated) {
    //                 $message->to($validated['client_email'])
    //                         ->subject('Set up your password - ATAI');
    //             });

    //             return response()->json([
    //                 'status' => 'success',
    //                 'data' => [
    //                     'client_id' => $clientId,
    //                     'message' => 'Client added and user created. Verification email sent.'
    //                 ]
    //             ]);
    //         } else {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'data' => [
    //                     'client_id' => null,
    //                     'message' => 'Failed to insert client.'
    //                 ]
    //             ]);
    //         }

    //     } catch (\Exception $e) {
    //         Log::error("Insert Client Error: " . $e->getMessage());

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Exception: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function insertClient(Request $request)
    {
        try {
            $validated = $request->validate([
                'client_name' => 'required|string|max:300',
                'client_gstin' => 'nullable|string|max:50',
                'client_email' => 'required|email|max:300',
                'client_contact_number' => 'required|string|max:50',
                'client_address' => 'nullable|string|max:1500',
                'client_description' => 'nullable|string|max:1500',
                'client_no_of_emp' => 'nullable|integer',
                'client_profile_photo' => 'nullable|string|max:2000',
            ]);

            DB::statement("SET @client_id = 0;");
            DB::statement("SET @message = '';");

            DB::select("CALL manage_clients(
                :action_type,
                @client_id,
                :client_name,
                :client_gstin,
                :client_email,
                :client_contact_number,
                :client_address,
                :client_description,
                :client_no_of_emp,
                :client_profile_photo,
                @message,
                NULL,
                NULL
            )", [
                'action_type' => 'I',
                'client_name' => $validated['client_name'],
                'client_gstin' => $validated['client_gstin'] ?? '',
                'client_email' => $validated['client_email'],
                'client_contact_number' => $validated['client_contact_number'],
                'client_address' => $validated['client_address'] ?? '',
                'client_description' => $validated['client_description'] ?? '',
                'client_no_of_emp' => $validated['client_no_of_emp'] ?? 0,
                'client_profile_photo' => $validated['client_profile_photo'] ?? '',
            ]);

            $results = DB::select('SELECT @client_id as client_id');
            $clientId = $results[0]->client_id ?? null;

            if ($clientId) {
                // $token = Str::uuid()->toString(); // Still generate token for backend validation

                $token = null;
                // DB::table('users')->insert([
                    $userId = DB::table('users')->insertGetId([
                    'user_name'     => $validated['client_name'],
                    'email'         => $validated['client_email'],
                    'mobile'        => $validated['client_contact_number'],
                    'profile_pic'   => $validated['client_profile_photo'] ?? '',
                    'status'        => 1,
                    'token'         => '',
                    'otp'           => null,
                    'is_verified'   => 0,
                    'is_available'  => 1,
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                    'ClientId'      => $clientId,
                    'abbreviation'  => 'A',
                    'PANNumber'     => null,
                    'DocPath'       => null,
                ]);

                // Static setup password link (no token in URL)
                $setupLink = "https://dev.atai.admin.raghavsolars.com/setup-password/". $userId;

                // Send email
                Mail::raw("Hi {$validated['client_name']},\n\nWelcome to ATAI! Please set up your password by visiting the link below:\n\n$setupLink\n\nThanks,\nATAI Team", function ($message) use ($validated) {
                    $message->to($validated['client_email'])
                            ->subject('Set up your password - ATAI');
                });

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'client_id' => $clientId,
                        'message' => 'Client added and user created. Verification email sent.'
                    ]
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'data' => [
                        'client_id' => null,
                        'message' => 'Failed to insert client.'
                    ]
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Insert Client Error: " . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Exception: ' . $e->getMessage(),
            ], 500);
        }
    }
}

