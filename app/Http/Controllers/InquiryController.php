<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UserInquiry;
use Carbon\Carbon;


use App\Models\QuestionText; // Add this for the Question model


class InquiryController extends Controller
{
    public function manageInquiry(Request $request)
    {
        try {
            // Step 1: Validate
            $validated = $request->validate([
                'Client_name' => 'required|string|max:50',
                'contact' => 'nullable|string|max:15',
                'email' => 'nullable|string|email|max:100',
                'last_question' => 'nullable|integer',
                'agent_remarks' => 'nullable|string|max:5000',
                'Next_followup' => 'nullable|date'
            ]);
    
            $status = 'OPN';
            $page_size = 0;
            $page = 1;
    
            // Step 2: Get client_id from last_question
            $clientId = 1; // default
            if (!empty($validated['last_question'])) {
                $clientIdFromQuestion = DB::table('questions')
                    ->where('id', $validated['last_question'])
                    ->value('client_id');
    
                if ($clientIdFromQuestion) {
                    $clientId = $clientIdFromQuestion;
                }
            }
    
            // Step 3: Find all inquiries related to this client_id via questions
            $relatedQuestionIds = DB::table('questions')
                ->where('client_id', $clientId)
                ->pluck('id');
    
            $maxUserId = DB::table('inquiry')
                ->whereIn('last_question', $relatedQuestionIds)
                ->select(DB::raw("MAX(CAST(User_id AS UNSIGNED)) as max_id"))
                ->value('max_id');
    
            $nextUserId = str_pad((int)$maxUserId + 1, 4, '0', STR_PAD_LEFT);
    
            // Step 4: Call stored procedure
            DB::select('CALL manage_inquiry(?, @p_id, ?, ?, ?, ?, ?, ?, ?, ?, @action_message, @affected_rows, ?, ?, ?)', [
                'I',
                $status,
                $nextUserId,
                $validated['Client_name'],
                $validated['contact'] ?? null,
                $validated['email'] ?? null,
                $validated['last_question'] ?? null,
                $validated['agent_remarks'] ?? null,
                $validated['Next_followup'] ?? null,
                $page_size,
                $page,
                $clientId
            ]);
    
            $output = DB::select('SELECT @action_message as message, @affected_rows as affected')[0];
    
            return response()->json([
                'success' => true,
                'message' => $output->message,
                'affected_rows' => $output->affected,
                'User_id' => $nextUserId,
                'Client_id' => $clientId
            ], 201);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    

    // public function manageInquiry(Request $request)
    // {
    //     $actionType = $request->input('action_type');
    //     $p_id = $request->input('p_id') ?? 0;
    //     $p_status = $request->input('p_status') ?? 'OPN';  // Default status 'OPN'
    //     $p_User_id = $request->input('p_User_id');
    //     $p_Client_name = $request->input('p_Client_name');
    //     $p_contact = $request->input('p_contact');
    //     $p_email = $request->input('p_email');
    //     $p_last_question = $request->input('p_last_question');
    //     $p_agent_remarks = $request->input('p_agent_remarks');
    //     $p_Next_followup = $request->input('p_Next_followup');
    //     $p_page_size = $request->input('p_page_size') ?? 10;
    //     $p_page = $request->input('p_page') ?? 1;
    //     $p_Client_id = $request->input('p_Client_id');

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
    //         'data' => $result,
    //         'message' => $actionMessage[0]->action_message ?? '',
    //         'affected_rows' => $affectedRows[0]->affected_rows ?? 0
    //     ]);
    // }

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

//         $p_page_size = request()->input('page_size', 10);   // Dynamic page size
//         $p_page = request()->input('page', 1);             // Dynamic page number
//         $p_Client_id = $client_id;

//         // Fetch all records without pagination
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
//             1000000, // Very large page size to fetch all inquiries
//             1,       // First page to get all
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

//         // Sort globally by created_at (or the timestamp column name)
//         usort($result, function ($a, $b) {
//             return strtotime($b->created_at) <=> strtotime($a->created_at);
//         });

//         // Paginate manually after sorting
//         $offset = ($p_page - 1) * $p_page_size;
//         $paginatedData = array_slice($result, $offset, $p_page_size);

//         return response()->json([
//             'status' => true,
//             'message' => $actionMessage[0]->action_message ?? 'Records fetched successfully.',
//             'data' => $paginatedData,
//             'total_records' => count($result),
//             'page' => $p_page,
//             'page_size' => $p_page_size,
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

        $p_page_size = request()->input('page_size', 10);
        $p_page = request()->input('page', 1);
        $p_Client_id = $client_id;

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
            1000000,
            1,
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

        // Sort globally by created_at
        usort($result, function ($a, $b) {
            return strtotime($b->created_at) <=> strtotime($a->created_at);
        });

        // Format the User_id in DDMMYY-XXXX format
        $result = array_map(function($item) {
            // Get the date in ddmmyy format (without hyphens)
            $date = date('dmy', strtotime($item->created_at ?? now()));
            
            // Get the User_id and format it to 4 digits with leading zeros
            $userId = str_pad($item->User_id ?? '0', 4, '0', STR_PAD_LEFT);
            
            // Combine them in the required format (DDMMYY-XXXX)
            $item->User_id = $date . '-' . $userId;
            
            return $item;
        }, $result);

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

// public function getUserInquiry($user_id)
// {
//     try {
//         // Find the user inquiry data
//         $inquiry = UserInquiry::where('user_id', $user_id)
//             ->select('client_name', 'contact', 'email')
//             ->first();

//         if (!$inquiry) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'No inquiry found for this user ID',
//                 'data' => null
//             ], 404);
//         }

//         return response()->json([
//             'status' => 'success',
//             'message' => 'User inquiry data retrieved successfully',
//             'data' => $inquiry
//         ]);

//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Failed to retrieve user inquiry data',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }



public function getUserInquiry(Request $request)
{
    try {
        // Extract values from request body
        $user_id = $request->input('user_id');
        $client_id = $request->input('client_id');

        // Validate required inputs
        if (!$user_id || !$client_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'user_id and client_id are required.',
                'data' => null
            ], 422);
        }

        // Join inquiry with questions to filter by client_id from questions table
        $inquiry = UserInquiry::with(['lastQuestion' => function($query) {
                $query->select('id', 'question_text');
            }])
            ->where('user_id', $user_id)
            ->whereHas('lastQuestion', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            })
            ->select('id', 'client_name', 'contact', 'email', 'last_question')
            ->first();

        if (!$inquiry) {
            return response()->json([
                'status' => 'error',
                'message' => 'No inquiry found for this user ID and client ID.',
                'data' => null
            ], 404);
        }

        // Format the response
        $response = [
            'id' => $inquiry->id,
            'client_name' => $inquiry->client_name,
            'contact' => $inquiry->contact,
            'email' => $inquiry->email,
            'last_question' => [
                'id' => $inquiry->last_question,
                'text' => $inquiry->lastQuestion->question_text ?? null
            ]
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'User inquiry data retrieved successfully',
            'data' => $response
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to retrieve user inquiry data',
            'error' => $e->getMessage()
        ], 500);
    }
}



public function getNewInquiries($client_id)
{
    $recentTime = Carbon::now()->subHours(24); // last 24 hours

    $count = DB::table('inquiry as iq')
        ->leftJoin('questions as qu', 'qu.id', '=', 'iq.last_question')
        ->where('qu.client_id', $client_id)
        ->where('iq.status', 'OPN') // <-- filter by open status
        ->where('iq.created_at', '>=', $recentTime)
        ->count();

    return response()->json([
        'client_id' => $client_id,
        'new_inquiries_count' => $count
    ]);
}


public function getRecentInquiries($client_id)
{
    $recentTime = Carbon::now()->subHours(24); // Last 24 hours

    $inquiries = DB::table('inquiry as iq')
        ->leftJoin('questions as qu', 'qu.id', '=', 'iq.last_question')
        ->select('iq.*', 'qu.client_id')
        ->where('qu.client_id', $client_id)
        ->where('iq.status', 'OPN') // <-- filter by open status
        ->where('iq.created_at', '>=', $recentTime)
        ->orderBy('iq.created_at', 'desc')
        ->get();

    return response()->json([
        'client_id' => $client_id,
        'new_inquiries_count' => $inquiries->count(),
        'recent_inquiries' => $inquiries
    ]);
 }

// public function getRecentInquiries($client_id)
// {
//     $recentTime = Carbon::now()->subHours(24); // Last 24 hours

//     $latestCreatedAt = DB::table('inquiry as iq')
//         ->leftJoin('questions as qu', 'qu.id', '=', 'iq.last_question')
//         ->where('qu.client_id', $client_id)
//         ->where('iq.status', 'OPN')
//         ->where('iq.created_at', '>=', $recentTime)
//         ->max('iq.created_at'); // <-- Get the most recent creation time

//     $inquiries = DB::table('inquiry as iq')
//         ->leftJoin('questions as qu', 'qu.id', '=', 'iq.last_question')
//         ->select('iq.*', 'qu.client_id')
//         ->where('qu.client_id', $client_id)
//         ->where('iq.status', 'OPN')
//         ->where('iq.created_at', $latestCreatedAt) // <-- Only the most recent one
//         ->orderBy('iq.created_at', 'desc')
//         ->get();

//     return response()->json([
//         'client_id' => $client_id,
//         'new_inquiries_count' => $inquiries->count(),
//         'recent_inquiries' => $inquiries
//     ]);
// }



}
