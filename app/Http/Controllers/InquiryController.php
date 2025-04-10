<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function updateInquiry(Request $request)
{
    $actionType = 'U'; // For Update
    $p_id = $request->input('p_id');
    $statusDescription = $request->input('p_status'); // Pass status name from frontend
    $p_User_id = $request->input('p_User_id');
    $p_Client_name = $request->input('p_Client_name');
    $p_contact = $request->input('p_contact');
    $p_email = $request->input('p_email');
    $p_last_question = $request->input('p_last_question');
    $p_agent_remarks = $request->input('p_agent_remarks');
    $p_Next_followup = $request->input('p_Next_followup');
    $p_page_size = 10;
    $p_page = 1;
    $p_Client_id = 0;

    // Fetch Status Code from status table
    $statusCode = DB::table('status')
        ->where('description', $statusDescription)
        ->where('entity', 'INQ')   // INQ for inquiry status
        ->value('code');

    if (!$statusCode) {
        return response()->json(['message' => 'Invalid Status Description'], 400);
    }

    // Call Stored Procedure
    $result = DB::select('CALL manage_inquiry(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @action_message, @affected_rows, ?, ?, ?)', [
        $actionType,
        $p_id,
        $statusCode,  // Pass code not description
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


public function deleteInquiry($id)
{
    $actionType = 'D';  // For Delete
    $p_id = $id;
    $p_status = '';  // Not Required in Delete
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
        'affected_rows' => $affectedRows[0]->affected_rows ?? 0
    ]);
}

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
        $p_page_size = 10;   // You can make it dynamic
        $p_page = 1;         // You can make it dynamic
        $p_Client_id = $client_id;

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

        if (empty($result)) {
            return response()->json([
                'status' => false,
                'message' => 'No records found for this Client ID.',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => $actionMessage[0]->action_message ?? 'Records fetched successfully.',
            'data' => $result
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong!',
            'error' => $e->getMessage()
        ], 500);
    }
}



}
