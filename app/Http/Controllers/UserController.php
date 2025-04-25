<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

public function manageUser(Request $request)
{
    // Validate input
    $validator = Validator::make($request->all(), [
        'p_mobile' => 'required|digits:10',
        'p_email' => 'nullable|email',
        'p_user_name' => 'required|string|max:255',
    ], [
        'p_mobile.required' => 'Mobile number is required.',
        'p_mobile.digits' => 'Mobile number must be exactly 10 digits.',
        'p_email.email' => 'Invalid email format.',
        'p_user_name.required' => 'User name is required.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed!',
            'errors' => $validator->errors()
        ], 422);
    }

    $action = $request->input('p_action');
    $userId = $request->input('p_user_id', 0);
    $message = '';

    if ($action === 'I' && $request->filled('p_email')) {
        $existingEmail = DB::table('users')
            ->where('email', $request->input('p_email'))
            ->exists();

        if ($existingEmail) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email already exists.',
            ], 409); // 409 = Conflict
        }
    }

    try {
        // Call the stored procedure
        $result = DB::select('CALL manage_user(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @message, ?, ?, ?, ?, ?, ?)', [
            $action,
            $userId,
            $request->input('p_user_name'),
            $request->input('p_email'),
            $request->input('p_mobile'),
            $request->input('p_profile_pic'),
            $request->input('p_status'),
            '',
            $request->input('p_otp'),
            $request->input('p_is_verified'),
            $request->input('p_is_available'),
            $request->input('P_pannumber'),
            $request->input('p_DocPath'),
            $request->input('p_role_abbreviation'),
            $request->input('p_ClientId'),
            $request->input('p_page_size'),
            $request->input('p_page'),
        ]);
        
        // Fetch stored procedure message
        $messageResult = DB::select('SELECT @message as message');
        $message = $messageResult[0]->message ?? 'Operation completed successfully.';

        // Retrieve the latest user ID
        $latestUser = DB::table('users')->orderBy('user_id', 'desc')->first();
        $latestUserId = $latestUser ? $latestUser->user_id : 633; 

        // Construct verification link
        $verificationLink = url("https://dev.atai.admin.raghavsolars.com/setup-password/{$latestUserId}");

        // Send email if email is provided
        if ($request->filled('p_email')) {
            $emailData = [
                'subject' => 'Verify Your Email for Agent Registration',
                'name' => $request->input('p_user_name'),
                'verification_link' => $verificationLink,
                'message' => "Dear {$request->input('p_user_name')},<br><br>
                    You have been added as an agent on ATai Chatbot. Please verify your email to complete the registration.<br>
                    Click the link below to verify your email:<br>
                    <a href='{$verificationLink}'>Verify Email</a><br><br>
                    Best regards,<br>
                    [Admin Name]"
            ];

            Mail::to($request->input('p_email'))->send(new SendMail($emailData));
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'verification_link' => $verificationLink, 
            'data' => $result
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Database error!',
            'error_details' => $e->getMessage()
        ], 500);
    }
}

public function updatePassword(Request $request)
{
    // Validate input
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,user_id',
        'password' => 'required|min:6|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed!',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Hash the password
        $hashedPassword = Hash::make($request->password);

        // Update password (stored in `token` column)
        DB::table('users')
            ->where('user_id', $request->user_id)
            ->update([
                'token' => $hashedPassword,
                'status' => 1 // optional: mark user as active after update
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully!',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Database error!',
            'error_details' => $e->getMessage()
        ], 500);
    }
}




public function getUserCredentials($user_id)
{
    try {
        // Fetch user credentials from user_data table
        $user = DB::table('users')
            ->select('email as username', 'token')
            ->where('user_id', $user_id)
            ->first();

        // Check if user exists
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found!',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Database error!',
            'error_details' => $e->getMessage()
        ], 500);
    }
}


public function verifyUserCredentials(Request $request)
{
    // Validate input (no client_id required)
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed!',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Fetch user by email - now including abbreviation and status
        $user = DB::table('users')
            ->select('user_id', 'email as username', 'token', 'ClientId', 'abbreviation', 'status')
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found!',
            ], 404);
        }

        // Check if user is active
        if ($user->status == 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot log in to this account!',
            ], 403);
        }

        // Check if password matches the hashed token
        if (!Hash::check($request->password, $user->token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Incorrect password!',
            ], 401);
        }

        // Determine dashboard access based on abbreviation
        $role = '';
        if ($user->abbreviation === 'A') {
            $role = 'admin';
        } elseif ($user->abbreviation === 'S') {
            $role = 'agent';
        } 

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful!',
            'data' => [
                'user_id'     => $user->user_id,
                'username'   => $user->username,
                'client_id'   => $user->ClientId,
                'role'   => $role,
                'abbreviation' => $user->abbreviation // optional, if you want to send it to frontend
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Database error!',
            'error_details' => $e->getMessage()
        ], 500);
    }
}


// public function verifyUserCredentials(Request $request)
// {
//     // Validate input (no client_id required)
//     $validator = Validator::make($request->all(), [
//         'email' => 'required|email',
//         'password' => 'required',
//     ]);

//     if ($validator->fails()) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Validation failed!',
//             'errors' => $validator->errors()
//         ], 422);
//     }

//     try {
//         // Fetch user by email - now including abbreviation
//         $user = DB::table('users')
//             ->select('user_id', 'email as username', 'token', 'ClientId', 'abbreviation')
//             ->where('email', $request->email)
//             ->first();

//         if (!$user) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'User not found!',
//             ], 404);
//         }

//         // Check if password matches the hashed token
//         if (!Hash::check($request->password, $user->token)) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Incorrect password!',
//             ], 401);
//         }

//         // Determine dashboard access based on abbreviation
//         $role = '';
//         if ($user->abbreviation === 'A') {
//             $role = 'admin';
//         } elseif ($user->abbreviation === 'S') {
//             $role = 'agent';
//         } 

//         return response()->json([
//             'status' => 'success',
//             'message' => 'Login successful!',
//             'data' => [
//                 'user_id'     => $user->user_id,
//                 'username'   => $user->username,
//                 'client_id'   => $user->ClientId,
//                 'role'   => $role,
//                 'abbreviation' => $user->abbreviation // optional, if you want to send it to frontend
//             ]
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Database error!',
//             'error_details' => $e->getMessage()
//         ], 500);
//     }
// }

    public function updateUser(Request $request)
    {
        $userId = $request->input('p_user_id');
        if (!$userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'User ID is required for update'
            ], 400);
        }

        $message = '';

        try {
            $result = DB::select('CALL manage_user(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @message, ?, ?, ?, ?, ?, ?)', [
                'U', // Update action
                $userId,
                $request->input('p_user_name'),
                $request->input('p_email'),
                $request->input('p_mobile'),
                $request->input('p_profile_pic'),
                $request->input('p_status'),
                $request->input('p_token'),
                $request->input('p_otp'),
                $request->input('p_is_verified'),
                $request->input('p_is_available'),
                $request->input('P_pannumber'),
                $request->input('p_DocPath'),
                $request->input('p_role_abbreviation'),
                $request->input('p_ClientId'),
                null, // p_page_size not needed for update, pass null
                null  // p_page not needed for update, pass null
            ]);

            // Fetch stored procedure message
            $messageResult = DB::select('SELECT @message as message');
            $message = $messageResult[0]->message ?? 'User updated successfully.';

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database error!',
                'error_details' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteUser(Request $request)
{
    $userId = $request->input('p_user_id');

    if (!$userId) {
        return response()->json([
            'status' => 'error',
            'message' => 'User ID is required for deletion'
        ], 400);
    }

    try {
        // Call stored procedure to delete from `users`
        DB::statement('CALL manage_user(?, ?, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, @message, NULL, NULL, NULL, NULL, NULL, NULL)', [
            'D', // Delete action
            $userId
        ]);

        // Delete from user_data table
        DB::table('users')->where('user_id', $userId)->delete();

        // Fetch stored procedure message
        $messageResult = DB::select('SELECT @message as message');
        $message = $messageResult[0]->message ?? 'User deleted successfully.';

        return response()->json([
            'status' => 'success',
            'message' => $message
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Database error during deletion!',
            'error_details' => $e->getMessage()
        ], 500);
    }
}


public function getUser(Request $request)
{
    try {
        $userId = $request->query('p_user_id');
        $email = $request->query('p_email');
        $mobile = $request->query('p_mobile');
        $panNumber = $request->query('p_PANNumber');
        $clientId = $request->query('p_ClientId'); // ✅ Added Client ID
        $pageSize = $request->query('p_page_size', 10); // ✅ Default to 10 if not provided
        $page = $request->query('p_page', 1); // ✅ Default to 1 if not provided

        // Ensure NULL is passed for missing parameters
        $results = DB::select('CALL manage_user(?, ?, ?, ?, ?, NULL, NULL, NULL, NULL, NULL, NULL, @message, NULL, NULL, NULL, ?, ?, ?)', [
            'G', // Action for GET
            $userId ?: NULL,
            $email ?: NULL,
            $mobile ?: NULL,
            $panNumber ?: NULL,
            $clientId ?: NULL ,// ✅ Passed Client ID to the stored procedure
            $pageSize,
            $page
        ]);

        // ✅ Filter only abbreviation 'S'
        $filteredResults = array_filter($results, function ($item) {
            return isset($item->abbreviation) && $item->abbreviation === 'S';
        });

        // Fetch the output message
        $messageResult = DB::select("SELECT @message AS message");
        $message = $messageResult[0]->message ?? 'Something went wrong!';

        // ✅ Check if no user is found after filtering
        if (empty($filteredResults)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No users found with abbreviation S!',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => array_values($filteredResults) // ✅ Reset array keys
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong!',
            'error_details' => $e->getMessage()
        ], 500);
    }
}

// public function getUser(Request $request)
// {
//     try {
//         $userId = $request->query('p_user_id');
//         $email = $request->query('p_email');
//         $mobile = $request->query('p_mobile');
//         $panNumber = $request->query('p_PANNumber');
//         $clientId = $request->query('p_ClientId'); // ✅ Added Client ID
//         $pageSize = $request->query('p_page_size', 10); // ✅ Default to 10 if not provided
//         $page = $request->query('p_page', 1); // ✅ Default to 1 if not provided

//         // Ensure NULL is passed for missing parameters
//         $results = DB::select('CALL manage_user(?, ?, ?, ?, ?, NULL, NULL, NULL, NULL, NULL, NULL, @message, NULL, NULL, NULL, ?, ?, ?)', [
//             'G', // Action for GET
//             $userId ?: NULL,
//             $email ?: NULL,
//             $mobile ?: NULL,
//             $panNumber ?: NULL,
//             $clientId ?: NULL ,// ✅ Passed Client ID to the stored procedure
//             $pageSize,
//             $page
//         ]);

//         // Fetch the output message
//         $messageResult = DB::select("SELECT @message AS message");
//         $message = $messageResult[0]->message ?? 'Something went wrong!';

//         // ✅ Check if no user is found
//         if (empty($results)) {
//             // Check if the issue is with Client ID
//             if ($clientId) {
//                 return response()->json([
//                     'status' => 'error',
//                     'message' => 'Client ID not found!',
//                 ], 404);
//             }
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'User not found!',
//             ], 404);
//         }

//         return response()->json([
//             'status' => 'success',
//             'message' => $message,
//             'data' => $results
//         ]);

//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Something went wrong!',
//             'error_details' => $e->getMessage()
//         ], 500);
//     }
// }

public function deleteUserByEmail(Request $request)
{
    // Validate input
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:user_data,email',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed!',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Delete user from user_data table
        $deleted = DB::table('user_data')->where('email', $request->email)->delete();

        if ($deleted) {
            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully!'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'User not found!'
        ], 404);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Database error!',
            'error_details' => $e->getMessage()
        ], 500);
    }
}




}
