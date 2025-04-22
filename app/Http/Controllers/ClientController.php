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
                    'status'        => 0,
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
                // Mail::raw("Hi {$validated['client_name']},\n\nWelcome to ATAI! Please set up your password by visiting the link below:\n\n$setupLink\n\nThanks,\nATAI Team", function ($message) use ($validated) {
                //     $message->to($validated['client_email'])
                //             ->subject('Set up your password - ATAI');
                // });
                Mail::send([], [], function ($message) use ($validated, $setupLink) {
                    $message->to($validated['client_email'])
                        ->subject('Set up your password - ATAI')
                        ->setBody("
                            <html>
                                <body>
                                    <p>Hi {$validated['client_name']},</p>
                                    <p>Welcome to ATAI! Please set up your password by clicking the button below:</p>
                                    <p>
                                        <a href='{$setupLink}' 
                                           style='display: inline-block; padding: 10px 20px; font-size: 16px; 
                                                  color: white; background-color: #1a73e8; text-decoration: none; 
                                                  border-radius: 5px;'>
                                            Set Up Password
                                        </a>
                                    </p>
                                    <p>Thanks,<br>ATAI Team</p>
                                </body>
                            </html>
                        ", 'text/html');
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

    public function getAllClients(Request $request)
{
    try {
        $clients = DB::table('clients')->select(
            'id',
            'Name',
            'GSTIN',
            'Email',
            'ContactNumber',
            'Address',
            'description',
            'NoofEmp',
            'ProfilePhoto'
        )->get();

        return response()->json([
            'status' => 'success',
            'message' => 'All clients fetched successfully',
            'data' => $clients
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Exception: ' . $e->getMessage(),
        ], 500);
    }
}

// public function deleteClientByEmail(Request $request)
// {
//     $request->validate([
//         'email' => 'required|email'
//     ]);

//     try {
//         $email = $request->input('email');

//         // Step 1: Get the client ID from email
//         $client = DB::table('clients')->where('Email', $email)->first();

//         if (!$client) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Client not found for the given email.'
//             ], 404);
//         }

//         $clientId = $client->id;

//         // Step 2: Prepare session variables
//         DB::statement("SET @client_id = ?", [$clientId]);
//         DB::statement("SET @message = ''");

//         // Step 3: Call the stored procedure with action_type = 'D'
//         DB::select("CALL manage_clients(
//             :action_type,
//             @client_id,
//             NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,
//             @message,
//             NULL, NULL
//         )", [
//             'action_type' => 'D'
//         ]);

//         // Step 4: Delete the user based on email
//         DB::table('users')->where('email', $email)->delete();

//         // Step 5: Return success
//         return response()->json([
//             'status' => 'success',
//             'message' => 'Client and related user deleted successfully.'
//         ]);

//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Exception: ' . $e->getMessage()
//         ], 500);
//     }
// }


public function deleteClientByEmail(Request $request)
{
    $request->validate([
        'email' => 'required|email'
    ]);

    try {
        DB::beginTransaction();

        $email = $request->input('email');

        // Step 1: Get the client using email
        $client = DB::table('clients')->where('Email', $email)->first();

        if (!$client) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found for the given email.'
            ], 404);
        }

        $clientId = $client->id;

        // Step 2: Delete user from users table
        DB::table('users')->where('ClientId', $clientId)->delete();

        // Step 3: Call stored procedure to delete client
        DB::statement("SET @client_id = ?", [$clientId]);
        DB::statement("SET @message = ''");

        DB::select("CALL manage_clients(
            :action_type,
            @client_id,
            NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,
            @message,
            NULL, NULL
        )", [
            'action_type' => 'D'
        ]);

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Client and user deleted successfully.'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();

        Log::error("Client Delete Error: " . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Exception: ' . $e->getMessage()
        ], 500);
    }
}

// public function updateClient(Request $request)
// {
//     try {
//         $validated = $request->validate([
//             'client_id' => 'required|integer',
//             'client_name' => 'required|string|max:300',
//             'client_gstin' => 'nullable|string|max:50',
//             'client_email' => 'required|email|max:300',
//             'client_contact_number' => 'required|string|max:50',
//             'client_address' => 'nullable|string|max:1500',
//             'client_description' => 'nullable|string|max:1500',
//             'client_no_of_emp' => 'nullable|integer',
//             'client_profile_photo' => 'nullable|string|max:2000',
//             'add_employee' => 'nullable|boolean',
//         ]);

//         $clientId = $validated['client_id'];

//         // Increment employee count if flagged
//         if (!empty($validated['add_employee'])) {
//             DB::table('clients')->where('id', $clientId)->increment('NoofEmp');
//         }

//         DB::statement("SET @message = '';");

//         // Call stored procedure for update
//         DB::select("CALL manage_clients(
//             :action_type,
//             :client_id,
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
//             'action_type' => 'U',
//             'client_id' => $clientId,
//             'client_name' => $validated['client_name'],
//             'client_gstin' => $validated['client_gstin'] ?? '',
//             'client_email' => $validated['client_email'],
//             'client_contact_number' => $validated['client_contact_number'],
//             'client_address' => $validated['client_address'] ?? '',
//             'client_description' => $validated['client_description'] ?? '',
//             'client_no_of_emp' => $validated['client_no_of_emp'] ?? 0,
//             'client_profile_photo' => $validated['client_profile_photo'] ?? '',
//         ]);

//         // Fetch existing user linked with this client
//         $user = DB::table('users')->where('ClientId', $clientId)->first();

//         if ($user) {
//             // Check if email was changed
//             $emailChanged = $user->email !== $validated['client_email'];

//             DB::table('users')
//                 ->where('ClientId', $clientId)
//                 ->update([
//                     'user_name'   => $validated['client_name'],
//                     'email'       => $validated['client_email'],
//                     'mobile'      => $validated['client_contact_number'],
//                     'profile_pic' => $validated['client_profile_photo'] ?? '',
//                     'updated_at'  => now(),
//                 ]);

//             if ($emailChanged) {
//                 $setupLink = "https://dev.atai.admin.raghavsolars.com/setup-password/" . $user->id;

//                 Mail::raw("Hi {$validated['client_name']},\n\nYour email was updated. Please set your new password using the link below:\n\n$setupLink\n\nThanks,\nATAI Team", function ($message) use ($validated) {
//                     $message->to($validated['client_email'])
//                             ->subject('Update your password - ATAI');
//                 });
//             }
//         }

//         return response()->json([
//             'status' => 'success',
//             'message' => 'Client and user info updated successfully.'
//         ]);
//     } catch (\Exception $e) {
//         Log::error("Update Client Error: " . $e->getMessage());

//         return response()->json([
//             'status' => 'error',
//             'message' => 'Exception: ' . $e->getMessage(),
//         ], 500);
//     }
// }
public function updateClient(Request $request)
{
    try {
        $validated = $request->validate([
            'client_id' => 'required|integer',
            'client_name' => 'required|string|max:300',
            'client_gstin' => 'nullable|string|max:50',
            'client_email' => 'required|email|max:300',
            'client_contact_number' => 'required|string|max:50',
            'client_address' => 'nullable|string|max:1500',
            'client_description' => 'nullable|string|max:1500',
            'client_profile_photo' => 'nullable|string|max:2000',
        ]);

        DB::statement("SET @client_id = :client_id", ['client_id' => $validated['client_id']]);
        DB::statement("SET @message = ''");

        DB::select("CALL manage_clients(
            :action_type,
            @client_id,
            :client_name,
            :client_gstin,
            :client_email,
            :client_contact_number,
            :client_address,
            :client_description,
            0,
            :client_profile_photo,
            @message,
            NULL,
            NULL
        )", [
            'action_type' => 'U',
            'client_name' => $validated['client_name'],
            'client_gstin' => $validated['client_gstin'] ?? '',
            'client_email' => $validated['client_email'],
            'client_contact_number' => $validated['client_contact_number'],
            'client_address' => $validated['client_address'] ?? '',
            'client_description' => $validated['client_description'] ?? '',
            'client_profile_photo' => $validated['client_profile_photo'] ?? '',
        ]);

        // Fetch the user linked with this client
        $user = DB::table('users')
            ->select('email')
            ->where('ClientId', $validated['client_id'])
            ->first();

        if ($user) {
            $emailChanged = $user->email !== $validated['client_email'];

            DB::table('users')->where('ClientId', $validated['client_id'])->update([
                'email'       => $validated['client_email'],
                'user_name'   => $validated['client_name'],
                'mobile'      => $validated['client_contact_number'],
                'profile_pic' => $validated['client_profile_photo'] ?? '',
                'updated_at'  => now(),
            ]);

            if ($emailChanged) {
                // Fetch user's ID manually (since no 'id' column)
                $userRecord = DB::table('users')->where('ClientId', $validated['client_id'])->first();

                $setupLink = "https://dev.atai.admin.raghavsolars.com/setup-password/{$validated['client_id']}";

                Mail::raw("Hi {$validated['client_name']},\n\nYour email has been updated. Please reset your password:\n\n$setupLink", function ($message) use ($validated) {
                    $message->to($validated['client_email'])
                            ->subject('Password Reset - ATAI');
                });
            }
        }

        // Update NoofEmp in client table by counting users with this ClientId
        $employeeCount = DB::table('users')->where('ClientId', $validated['client_id'])->count();

        DB::table('clients')->where('id', $validated['client_id'])->update([
            'NoofEmp' => $employeeCount
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Client updated successfully, user synced, and employee count updated.',
        ]);
    } catch (\Exception $e) {
        \Log::error("Update Client Error: " . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Exception: ' . $e->getMessage()
        ], 500);
    }
}


}

