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
}
