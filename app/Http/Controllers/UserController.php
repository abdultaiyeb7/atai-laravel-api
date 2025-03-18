<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function manageUser(Request $request)
    {
        $action = $request->input('p_action');
        $userId = $request->input('p_user_id', 0);
        $message = '';

        // Call the stored procedure
        $result = DB::select('CALL manage_user(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $action,
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
            &$message, // Output parameter
            $request->input('P_pannumber'),
            $request->input('p_DocPath'),
            $request->input('p_role_abbreviation'),
            $request->input('p_ClientId'),
        ]);

        // Set the response message
        if ($action === 'I') {
            $message = "User " . $userId . " inserted successfully.";
        } elseif ($action === 'U') {
            $message = "User " . $userId . " updated successfully.";
        }

        return response()->json([
            'message' => $message,
            'data' => $result
        ]);
    }

    public function updateUser(Request $request)
    {
        $userId = $request->input('p_user_id');
        $message = '';

        if (!$userId) {
            return response()->json([
                'error' => 'User ID is required for update'
            ], 400);
        }

        // Call the stored procedure
        $result = DB::select('CALL manage_user(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            'U', // Action for update
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
            &$message, // Output parameter
            $request->input('P_pannumber'),
            $request->input('p_DocPath'),
            $request->input('p_role_abbreviation'),
            $request->input('p_ClientId'),
        ]);

        // Set the response message
        $message = "User with ID " . $userId . " updated successfully.";

        return response()->json([
            'message' => $message,
            'data' => $result
        ]);
    }

    public function deleteUser(Request $request)
{
    $userId = $request->input('p_user_id');

    if (!$userId) {
        return response()->json([
            'error' => 'User ID is required for deletion'
        ], 400);
    }

    // Call the stored procedure
    DB::statement('CALL manage_user(?, ?, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, @message, NULL, NULL, NULL, NULL)', [
        'D', // Action for delete
        $userId
    ]);

    // Retrieve the message from the session variable
    $messageResult = DB::select('SELECT @message as message');
    $message = $messageResult[0]->message ?? 'No message returned';

    return response()->json([
        'message' => $message
    ]);
}

// public function getUser(Request $request)
// {
//     try {
//         $userId = $request->query('p_user_id');
//         $email = $request->query('p_email');
//         $mobile = $request->query('p_mobile');
//         $panNumber = $request->query('p_PANNumber');

//         // Call the stored procedure
//         $users = DB::select('CALL manage_user(?, ?, ?, ?, ?, NULL, NULL, NULL, NULL, NULL, NULL, @message, ?, NULL, NULL, NULL)', [
//             'G', // Action for get
//             $userId,
//             $email,
//             $mobile,
//             $panNumber
//         ]);

//         // Retrieve the message from MySQL
//         $messageResult = DB::select('SELECT @message as message');
//         $message = $messageResult[0]->message ?? 'Something went wrong!';

//         return response()->json([
//             'message' => $message,
//             'data' => $users
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'error' => 'Something went wrong!',
//             'details' => $e->getMessage()
//         ], 500);
//     }
// }

}