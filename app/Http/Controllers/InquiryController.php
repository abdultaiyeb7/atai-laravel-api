<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UserInquiry;

class InquiryController extends Controller
{
    public function manageInquiry(Request $request)
    {
        $actionType = $request->input('action_type');
        $p_id = $request->input('p_id') ?? 0;
        $p_status = $request->input('p_status') ?? 'OPN';  // Default status 'OPN'
        $p_User_id = $request->input('p_User_id');
        $p_Client_name = $request->input('p_Client_name');
        $p_contact = $request->input('p_contact');
        $p_email = $request->input('p_email');
        $p_last_question = $request->input('p_last_question');
        $p_agent_remarks = $request->input('p_agent_remarks');
        $p_Next_followup = $request->input('p_Next_followup');
        $p_page_size = $request->input('p_page_size') ?? 10;
        $p_page = $request->input('p_page') ?? 1;
        $p_Client_id = $request->input('p_Client_id');

        $result = DB::select('CALL manage_inquiry(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @action_message, @affected_rows, ?, ?, ?)', [
            $actionType,
            $p_id,
            $p_status,
            $p_User_id,
            $p_Client_name,
            $p_contact,
            $p_email,
            $p_last_question,
            $p_agent_remarks,
            $p_Next_followup,
            $p_page_size,
            $p_page,
            $p_Client_id
        ]);

        $actionMessage = DB::select('SELECT @action_message as action_message');
        $affectedRows = DB::select('SELECT @affected_rows as affected_rows');

        return response()->json([
            'data' => $result,
            'message' => $actionMessage[0]->action_message ?? '',
            'affected_rows' => $affectedRows[0]->affected_rows ?? 0
        ]);
    }

//     public function updateInquiry(Request $request)
// {
//     $actionType = 'U'; // For Update
//     $p_id = $request->input('p_id');
//     $statusDescription = $request->input('p_status'); // Pass status name from frontend
//     $p_User_id = $request->input('p_User_id');
//     $p_Client_name = $request->input('p_Client_name');
//     $p_contact = $request->input('p_contact');
//     $p_email = $request->input('p_email');
//     $p_last_question = $request->input('p_last_question');
//     $p_agent_remarks = $request->input('p_agent_remarks');
//     $p_Next_followup = $request->input('p_Next_followup');
//     $p_page_size = 10;
//     $p_page = 1;
//     $p_Client_id = 0;

//     // Fetch Status Code from status table
//     $statusCode = DB::table('status')
//         ->where('description', $statusDescription)
//         ->where('entity', 'INQ')   // INQ for inquiry status
//         ->value('code');

//     if (!$statusCode) {
//         return response()->json(['message' => 'Invalid Status Description'], 400);
//     }

//     // Call Stored Procedure
//     $result = DB::select('CALL manage_inquiry(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @action_message, @affected_rows, ?, ?, ?)', [
//         $actionType,
//         $p_id,
//         $statusCode,  // Pass code not description
//         $p_User_id,
//         $p_Client_name,
//         $p_contact,
//         $p_email,
//         $p_last_question,
//         $p_agent_remarks,
//         $p_Next_followup,
//         $p_page_size,
//         $p_page,
//         $p_Client_id
//     ]);

//     $actionMessage = DB::select('SELECT @action_message as action_message');
//     $affectedRows = DB::select('SELECT @affected_rows as affected_rows');

//     return response()->json([
//         'data' => $result,
//         'message' => $actionMessage[0]->action_message ?? '',
//         'affected_rows' => $affectedRows[0]->affected_rows ?? 0
//     ]);
// }

public function updateInquiry(Request $request)
{
    try {
        $actionType = 'U'; // For Update
        $p_id = $request->input('p_id');
        
        // Validate required fields
        if (!$p_id) {
            return response()->json(['message' => 'Inquiry ID is required'], 400);
        }

        // Get current inquiry data
        $currentInquiry = DB::table('inquiry')->where('id', $p_id)->first();
        
        if (!$currentInquiry) {
            return response()->json(['message' => 'Inquiry not found'], 404);
        }

        // Check if the current status is one of the restricted statuses
        $restrictedStatuses = ['No Response from Client so Closed', 'Closed', 'Resolved and Closed'];
        $currentStatus = DB::table('status')
                        ->where('code', $currentInquiry->status)
                        ->where('entity', 'INQ')
                        ->value('description');
        
        if (in_array($currentStatus, $restrictedStatuses)) {
            return response()->json([
                'message' => 'Cannot update inquiry with status: ' . $currentStatus
            ], 400);
        }

        // Get inputs - only status, agent_remarks, and next_followup are updatable
        $statusDescription = $request->input('p_status');
        $p_agent_remarks = $request->input('p_agent_remarks');
        $p_Next_followup = $request->input('p_Next_followup');

        // Validate at least one field is being updated
        if (!$statusDescription && !$p_agent_remarks && !$p_Next_followup) {
            return response()->json(['message' => 'At least one field (status, agent_remarks, or next_followup) must be provided for update'], 400);
        }

        // If status is being updated, validate it
        $statusCode = null;
        if ($statusDescription) {
            $statusCode = DB::table('status')
                ->where('description', $statusDescription)
                ->where('entity', 'INQ')
                ->value('code');

            if (!$statusCode) {
                return response()->json(['message' => 'Invalid Status Description'], 400);
            }
        } else {
            // Keep the current status if not being updated
            $statusCode = $currentInquiry->status;
        }

        // Use current values for fields that aren't being updated
        // Using null coalescing operator to handle potential null values
        $p_User_id = $currentInquiry->user_id ?? $currentInquiry->User_id ?? null;
        $p_Client_name = $currentInquiry->client_name ?? $currentInquiry->Client_name ?? null;
        $p_contact = $currentInquiry->contact ?? null;
        $p_email = $currentInquiry->email ?? null;
        $p_last_question = $currentInquiry->last_question ?? $currentInquiry->last_question ?? null;
        $p_Client_id = $currentInquiry->client_id ?? $currentInquiry->Client_id ?? 0;
        
        // If next_followup isn't provided, keep the current value or set to null if allowed
        if (!$p_Next_followup) {
            $p_Next_followup = $currentInquiry->next_followup ?? $currentInquiry->Next_followup ?? null;
        }

        // Handle agent remarks
        $agentRemarks = $p_agent_remarks ?? $currentInquiry->agent_remarks ?? $currentInquiry->agent_remarks ?? null;

        // Pagination parameters (not used for update but required by the procedure)
        $p_page_size = 10;
        $p_page = 1;

        // Call Stored Procedure
        $result = DB::select('CALL manage_inquiry(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @action_message, @affected_rows, ?, ?, ?)', [
            $actionType,
            $p_id,
            $statusCode,
            $p_User_id,
            $p_Client_name,
            $p_contact,
            $p_email,
            $p_last_question,
            $agentRemarks,
            $p_Next_followup,
            $p_page_size,
            $p_page,
            $p_Client_id
        ]);

        $actionMessage = DB::select('SELECT @action_message as action_message');
        $affectedRows = DB::select('SELECT @affected_rows as affected_rows');

        if (($affectedRows[0]->affected_rows ?? 0) === 0) {
            return response()->json([
                'message' => 'No changes made to the inquiry',
                'affected_rows' => 0
            ], 200);
        }

        return response()->json([
            'data' => $result,
            'message' => $actionMessage[0]->action_message ?? 'Inquiry updated successfully',
            'affected_rows' => $affectedRows[0]->affected_rows ?? 1
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to update inquiry',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString() // Only include this in development
        ], 500);
    }
}


// public function deleteInquiry($id)
// {
//     $actionType = 'D';  // For Delete
//     $p_id = $id;
//     $p_status = '';  // Not Required in Delete
//     $p_User_id = '';
//     $p_Client_name = '';
//     $p_contact = '';
//     $p_email = '';
//     $p_last_question = 0;
//     $p_agent_remarks = '';
//     $p_Next_followup = null;
//     $p_page_size = 10;
//     $p_page = 1;
//     $p_Client_id = 0;

//     // Call Stored Procedure
//     $result = DB::select('CALL manage_inquiry(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @action_message, @affected_rows, ?, ?, ?)', [
//         $actionType,
//         $p_id,
//         $p_status,
//         $p_User_id,
//         $p_Client_name,
//         $p_contact,
//         $p_email,
//         $p_last_question,
//         $p_agent_remarks,
//         $p_Next_followup,
//         $p_page_size,
//         $p_page,
//         $p_Client_id
//     ]);

//     $actionMessage = DB::select('SELECT @action_message as action_message');
//     $affectedRows = DB::select('SELECT @affected_rows as affected_rows');

//     return response()->json([
//         'message' => $actionMessage[0]->action_message ?? '',
//         'affected_rows' => $affectedRows[0]->affected_rows ?? 0
//     ]);
// }

public function deleteInquiry($id)
{
    // Check inquiry exists
    $inquiry = DB::table('inquiry')->where('id', $id)->first();

    if (!$inquiry) {
        return response()->json([
            'message' => 'Inquiry not found.',
            'status' => false
        ], 404);
    }

    // Fetch status code from inquiry table
    $status = $inquiry->status;

    // Validation based on status
    if ($status == 'OPN') {
        return response()->json([
            'message' => 'Cannot delete inquiry because it is Open.',
            'status' => false
        ], 400);
    }

    if (!in_array($status, ['CNR', 'CRS'])) {
        return response()->json([
            'message' => 'Inquiry cannot be deleted in this status.',
            'status' => false
        ], 400);
    }

    // Proceed to Delete
    $actionType = 'D';  // For Delete
    $p_id = $id;
    $p_status = '';  
    $p_User_id = '';
    $p_Client_name = '';
    $p_contact = '';
    $p_email = '';
    $p_last_question = 0;
    $p_agent_remarks = '';
    $p_Next_followup = null;
    $p_page_size = 10;
    $p_page = 1;
    $p_Client_id = 0;

    // Call Stored Procedure
    $result = DB::select('CALL manage_inquiry(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @action_message, @affected_rows, ?, ?, ?)', [
        $actionType,
        $p_id,
        $p_status,
        $p_User_id,
        $p_Client_name,
        $p_contact,
        $p_email,
        $p_last_question,
        $p_agent_remarks,
        $p_Next_followup,
        $p_page_size,
        $p_page,
        $p_Client_id
    ]);

    $actionMessage = DB::select('SELECT @action_message as action_message');
    $affectedRows = DB::select('SELECT @affected_rows as affected_rows');

    return response()->json([
        'message' => $actionMessage[0]->action_message ?? '',
        'affected_rows' => $affectedRows[0]->affected_rows ?? 0,
        'status' => true
    ]);
}


// public function getInquiryByClient($client_id)
// {
//     try {
//         $actionType = 'G';  // For Get
//         $p_id = null;
//         $p_status = null;
//         $p_User_id = null;
//         $p_Client_name = null;
//         $p_contact = null;
//         $p_email = null;
//         $p_last_question = null;
//         $p_agent_remarks = null;
//         $p_Next_followup = null;
//         // $p_page_size = 10;   // You can make it dynamic
//         // $p_page = 1;         // You can make it dynamic
//         // $p_Client_id = $client_id;


//         $p_page_size = request()->input('page_size', 10);   // Dynamic page size
//         $p_page = request()->input('page', 1);             // Dynamic page number
//         $p_Client_id = $client_id;
//         // Call Stored Procedure
//         $result = DB::select('CALL manage_inquiry(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @action_message, @affected_rows, ?, ?, ?)', [
//             $actionType,
//             $p_id,
//             $p_status,
//             $p_User_id,
//             $p_Client_name,
//             $p_contact,
//             $p_email,
//             $p_last_question,
//             $p_agent_remarks,
//             $p_Next_followup,
//             $p_page_size,
//             $p_page,
//             $p_Client_id
//         ]);

//         $actionMessage = DB::select('SELECT @action_message as action_message');
//         $affectedRows = DB::select('SELECT @affected_rows as affected_rows');

//         if (empty($result)) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'No records found for this Client ID.',
//                 'data' => []
//             ], 404);
//         }

//         return response()->json([
//             'status' => true,
//             'message' => $actionMessage[0]->action_message ?? 'Records fetched successfully.',
//             'data' => $result
//         ]);

//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => false,
//             'message' => 'Something went wrong!',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }


public function getInquiryByClient($client_id)
{
    try {
        $actionType = 'G';  // For Get
        $p_id = null;
        $p_status = null;
        $p_User_id = null;
        $p_Client_name = null;
        $p_contact = null;
        $p_email = null;
        $p_last_question = null;
        $p_agent_remarks = null;
        $p_Next_followup = null;

        $p_page_size = request()->input('page_size', 10);   // Dynamic page size
        $p_page = request()->input('page', 1);             // Dynamic page number
        $p_Client_id = $client_id;

        // Fetch all records without pagination
        $result = DB::select('CALL manage_inquiry(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @action_message, @affected_rows, ?, ?, ?)', [
            $actionType,
            $p_id,
            $p_status,
            $p_User_id,
            $p_Client_name,
            $p_contact,
            $p_email,
            $p_last_question,
            $p_agent_remarks,
            $p_Next_followup,
            1000000, // Very large page size to fetch all inquiries
            1,       // First page to get all
            $p_Client_id
        ]);

        $actionMessage = DB::select('SELECT @action_message as action_message');
        $affectedRows = DB::select('SELECT @affected_rows as affected_rows');

        if (empty($result)) {
            return response()->json([
                'status' => false,
                'message' => 'No records found for this Client ID.',
                'data' => []
            ], 404);
        }

        // Sort globally by created_at (or the timestamp column name)
        usort($result, function ($a, $b) {
            return strtotime($b->created_at) <=> strtotime($a->created_at);
        });

        // Paginate manually after sorting
        $offset = ($p_page - 1) * $p_page_size;
        $paginatedData = array_slice($result, $offset, $p_page_size);

        return response()->json([
            'status' => true,
            'message' => $actionMessage[0]->action_message ?? 'Records fetched successfully.',
            'data' => $paginatedData,
            'total_records' => count($result),
            'page' => $p_page,
            'page_size' => $p_page_size,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong!',
            'error' => $e->getMessage()
        ], 500);
    }
}


    // public function getStatusCount($client_id)
    // {
    //     try {
    //         $results = DB::select("
    //             SELECT inq.status, COUNT(inq.status) AS status_count
    //             FROM inquiry inq
    //             INNER JOIN questions qu ON qu.id = inq.last_question
    //             WHERE qu.client_id = ?
    //             GROUP BY inq.status
    //         ", [$client_id]);

    //         return response()->json([
    //             'success' => true,
    //             'data' => $results
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error fetching status count',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function getStatusCount($client_id)
{
    try {
        // Get status-wise count
        $statusCounts = DB::select("
            SELECT inq.status, COUNT(inq.status) AS status_count
            FROM inquiry inq
            INNER JOIN questions qu ON qu.id = inq.last_question
            WHERE qu.client_id = ?
            GROUP BY inq.status
        ", [$client_id]);

        // Calculate total count from status-wise result
        $totalCount = collect($statusCounts)->sum('status_count');

        return response()->json([
            'success' => true,
            'total_count' => $totalCount,
            'data' => $statusCounts
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching status count',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getUserInquiry($user_id)
{
    try {
        // Find the user inquiry data
        $inquiry = UserInquiry::where('user_id', $user_id)
            ->select('client_name', 'contact', 'email')
            ->first();

        if (!$inquiry) {
            return response()->json([
                'status' => 'error',
                'message' => 'No inquiry found for this user ID',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User inquiry data retrieved successfully',
            'data' => $inquiry
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to retrieve user inquiry data',
            'error' => $e->getMessage()
        ], 500);
    }
}



}
