<?php

namespace App\Http\Controllers;

use App\Models\TicketsData;
use App\Models\UserConvJourney;
use App\Models\UserInfo;
use App\Models\callback;
use App\Models\totalcount;
use App\Models\ticket_starred;
use App\Models\unStarTicket;
use App\Models\getStarredTicket;
use App\Models\UserQueryResolution;
use App\Models\updateCallbackRequestResolution;
use App\Models\resolveTicket;
use App\Models\getResolvedTicketCount;
use App\Models\getUnresolvedTicketCount;
use App\Models\getTicketResolvedTime;
use App\Models\ChatbotData;
use App\Models\saveRemarkAndFollowUp;
use App\Models\getRemarks;
use App\Models\getFollowUpTickets;
use App\Models\updateTicketResolution;
use App\Models\Status;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function getAllTicketsInfo(Request $request)
    {
        try {
            Log::info("Fetching all tickets information.");

            // Fetch all tickets
            $tickets = TicketsData::all();
            $tickets_info = [];

            // Define IST timezone
            $ist = new DateTimeZone('Asia/Kolkata');

            foreach ($tickets as $ticket) {
                Log::info("Processing ticket_id: " . $ticket->ticket_id);

                // Fetch the correct ticket title from the database
                $ticket_title = $this->getTicketTitle($ticket->ticket_id);
                Log::info("Ticket title fetched: " . $ticket_title);

                // Determine resolved time
                $resolved_time = $ticket->ticket_resolved ?: $ticket->ticket_created;
                $status = $ticket->ticket_resolution_status ?: "Pending";
                
                // Convert to IST
                $resolved_time_ist = Carbon::parse($resolved_time)->setTimezone($ist)->format('Y-m-d H:i:s');
                Log::info("Resolved time in IST: " . $resolved_time_ist);

                // Append to response
                $tickets_info[] = [
                    "ticket_id" => $ticket->ticket_id,
                    "ticket_title" => $ticket_title,
                    "updated" => $resolved_time_ist,
                    "status" => $status,
                    "agent_remarks" => $ticket->agent_remarks, // Ensure this field exists
                ];
            }

            return response()->json($tickets_info, 200);
        } catch (\Exception $e) {
            Log::error("An error occurred: " . $e->getMessage());
            return response()->json(["message" => "An error occurred while retrieving tickets information"], 500);
        }
    }

    public function getAllTicketsInfoapi(Request $request)
    {
        try {
            Log::info("Fetching all tickets information.");

            // Fetch all tickets
            $tickets = TicketsData::all();
            $tickets_info = [];

            // Define IST timezone
            $ist = new DateTimeZone('Asia/Kolkata');

            foreach ($tickets as $ticket) {
                Log::info("Processing ticket_id: " . $ticket->ticket_id);

                // Fetch the correct ticket title from the database
                $ticket_title = $this->getTicketTitle($ticket->ticket_id);
                Log::info("Ticket title fetched: " . $ticket_title);

                // Determine resolved time
                $resolved_time = $ticket->ticket_resolved ?: $ticket->ticket_created;
                $status = $ticket->ticket_resolution_status ?: "Pending";
                
                // Convert to IST
                $resolved_time_ist = Carbon::parse($resolved_time)->setTimezone($ist)->format('Y-m-d H:i:s');
                Log::info("Resolved time in IST: " . $resolved_time_ist);

                // Append to response
                $tickets_info[] = [
                    "ticket_id" => $ticket->ticket_id,
                    "ticket_title" => $ticket_title,
                    "updated" => $resolved_time_ist,
                    "status" => $status,
                    "agent_remarks" => $ticket->agent_remarks, // Ensure this field exists
                ];
            }

            return response()->json($tickets_info, 200);
        } catch (\Exception $e) {
            Log::error("An error occurred: " . $e->getMessage());
            return response()->json(["message" => "An error occurred while retrieving tickets information"], 500);
        }
    }

    private function getTicketTitle($ticketId)
    {
        // Fetch the ticket title from the database based on ticket_id
        $ticket = DB::table('tickets_data')->where('ticket_id', $ticketId)->first();

        if ($ticket && !empty($ticket->ticket_title)) {
            return $ticket->ticket_title;
        } else {
            return "Unknown Title"; // Default title if not found
        }
    }
    public function getUserConversation(Request $request)
    {
        // Get user_id from request body (JSON)
        $userId = $request->input('user_id');

        if (!$userId) {
            return response()->json(["message" => "User ID is required"], 400);
        }

        // Query the database
        $convJourney = UserConvJourney::where('user_conv_journey_id', $userId)->first();

        if (!$convJourney) {
            return response()->json(["message" => "User conversation not found"], 404);
        }

        return response()->json([
            "user_id" => $userId,
            "user_conversation" => $convJourney->user_conversation
        ], 200);
    }
    
    public function getTicketUserInfo(Request $request)
    {
        // Get ticket_id from request
        $ticketId = $request->query('ticket_id');

        if (!$ticketId) {
            return response()->json(["message" => "Ticket ID is required"], 400);
        }

        // Query the database
        $ticket = UserInfo::where('ticket_id', $ticketId)->first();

        if (!$ticket) {
            return response()->json(["message" => "Ticket not found"], 404);
        }

        return response()->json([
            "user_id" => $ticket->user_id,
            "user_name" => $ticket->user_name,
            "email" => $ticket->email,
            "contact" => $ticket->contact
        ], 200);
    }


    public function getAllCallbackRequests()
    {
        try {
            Log::info("Fetching all callback requests.");

            // Query tickets where callback_requested is true and ticket is not closed
            $callbackRequests = callback::where('callback_requested', true)
                ->where('ticket_resolution_status', '!=', 'Closed')
                ->get();

            // Prepare response
            $response = $callbackRequests->map(function ($ticket) {
                return [
                    "ticket_id" => $ticket->ticket_id,
                    "user_name" => $ticket->user_name,
                    "contact" => $ticket->contact,
                    "email" => $ticket->email,
                    "userquery" => $ticket->userquery ?? "", // Ensure it's not null
                ];
            });

            return response()->json($response, 200);
        } catch (\Exception $e) {
            Log::error("An error occurred: " . $e->getMessage());
            return response()->json(["message" => "An error occurred while retrieving callback requests"], 500);
        }
    }

    public function getTotalTicketCount()
    {
        try {
            Log::info("Fetching total ticket count.");

            // Count total tickets
            $totalTickets = totalcount::count();

            return response()->json(["ticket_count" => $totalTickets], 200);
        } catch (\Exception $e) {
            Log::error("An error occurred: " . $e->getMessage());
            return response()->json(["message" => "An error occurred while retrieving the total ticket count"], 500);
        }
    }

    public function starTicket(Request $request)
    {
        try {
            $ticketId = $request->input('ticket_id');

            if (!$ticketId) {
                return response()->json(["message" => "Ticket ID is required"], 400);
            }

            // Find the ticket
            $ticket = ticket_starred::where('ticket_id', $ticketId)->first();

            if (!$ticket) {
                return response()->json(["message" => "Ticket not found"], 404);
            }

            // Mark the ticket as starred
            $ticket->ticket_starred = true;
            $ticket->save();

            return response()->json(["message" => "Ticket starred successfully"], 200);
        } catch (\Exception $e) {
            Log::error("An error occurred: " . $e->getMessage());
            return response()->json(["message" => "An error occurred while starring the ticket"], 500);
        }
    }

    public function unStarTicket(Request $request)
    {
        try {
            $ticketId = $request->input('ticket_id');

            if (!$ticketId) {
                return response()->json(["message" => "Ticket ID is required"], 400);
            }

            // Find the ticket
            $ticket = unStarTicket::where('ticket_id', $ticketId)->first();

            if (!$ticket) {
                return response()->json(["message" => "Ticket not found"], 404);
            }

            // Mark the ticket as unstarred
            $ticket->ticket_starred = false;
            $ticket->save();

            return response()->json(["message" => "Ticket un-starred successfully"], 200);
        } catch (\Exception $e) {
            Log::error("An error occurred: " . $e->getMessage());
            return response()->json(["message" => "An error occurred while unstarring the ticket"], 500);
        }
    }

    public function getStarredTicketCount()
    {
        try {
            Log::info("Fetching count of starred tickets.");

            // Count starred tickets
            $starredTickets = getStarredTicket::where('ticket_starred', true)->count();

            return response()->json(["starred_ticket_count" => $starredTickets], 200);
        } catch (\Exception $e) {
            Log::error("An error occurred: " . $e->getMessage());
            return response()->json(["message" => "An error occurred while retrieving the starred ticket count"], 500);
        }
    }

    public function updateUserQueryResolutionStatus(Request $request)
    {
        try {
            $ticketId = $request->input('ticket_id');
            $status = $request->input('status');

            Log::info("Received ticket_id: $ticketId, status: $status"); // Debugging

            if (!$ticketId || !$status) {
                return response()->json(["message" => "Ticket ID and status are required"], 400);
            }

            // Find the ticket
            $ticket = UserQueryResolution::where('ticket_id', $ticketId)->first();

            if (!$ticket) {
                return response()->json(["message" => "Ticket not found"], 404);
            }

            if (!$ticket->userquery || trim($ticket->userquery) === "") {
                return response()->json(["message" => "User query not present. Please check the ticket ID again."], 409);
            }

            // Update the resolution status
            $ticket->userquery_resolution_status = $status;
            $ticket->save();

            return response()->json([
                "message" => "User query resolution status updated successfully",
                "ticket_id" => $ticketId,
                "userquery_resolution_status" => $status
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error: " . $e->getMessage());
            return response()->json(["message" => "Internal server error"], 500);
        }
    }

    public function updateCallbackRequestResolutionStatus(Request $request)
    {
        try {
            $ticketId = $request->input('ticket_id');
            $status = $request->input('status');

            Log::info("Received ticket_id: $ticketId, status: $status"); // Debugging

            if (!$ticketId || !$status) {
                return response()->json(["message" => "Ticket ID and status are required"], 400);
            }

            // Find the ticket
            $ticket = updateCallbackRequestResolution::where('ticket_id', $ticketId)->first();

            if (!$ticket) {
                return response()->json(["message" => "Ticket not found"], 404);
            }

            if (!$ticket->callback_requested) {
                return response()->json(["message" => "Callback not requested. Please check the ticket ID again."], 409);
            }

            // Update the callback request resolution status
            $ticket->callback_request_resolution_status = $status;
            $ticket->save();

            return response()->json([
                "message" => "Callback request resolution status updated successfully",
                "ticket_id" => $ticketId,
                "callback_request_resolution_status" => $status
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error: " . $e->getMessage());
            return response()->json(["message" => "Internal server error"], 500);
        }
    }

    public function resolveTicket(Request $request)
    {
        try {
            $ticketId = $request->input('ticket_id');

            Log::info("Resolving ticket: $ticketId"); // Debugging

            if (!$ticketId) {
                return response()->json(["message" => "Ticket ID is required"], 400);
            }

            // Find the ticket
            $ticket = resolveTicket::where('ticket_id', $ticketId)->first();

            if (!$ticket) {
                return response()->json(["message" => "Ticket not found"], 404);
            }

            // Check conditions based on userquery and callback_requested
            if ($ticket->userquery && !$ticket->callback_requested) {
                // Check userquery_resolution_status
                if ($ticket->userquery_resolution_status) {
                    $ticket->is_ticket_resolved = true;
                    $ticket->ticket_resolved = Carbon::now(); // Set current timestamp
                    $ticket->follow_up = null; // Set follow-up to null
                    $ticket->save();

                    return response()->json([
                        "message" => "Ticket resolved successfully",
                        "ticket_id" => $ticketId
                    ], 200);
                } else {
                    return response()->json(["message" => "User query is not resolved yet"], 412);
                }
            } 
            elseif ($ticket->callback_requested && !$ticket->userquery) {
                // Check callback_request_resolution_status
                if ($ticket->callback_request_resolution_status) {
                    $ticket->is_ticket_resolved = true;
                    $ticket->ticket_resolved = Carbon::now(); // Set current timestamp
                    $ticket->follow_up = null; // Set follow-up to null
                    $ticket->save();

                    return response()->json([
                        "message" => "Ticket resolved successfully",
                        "ticket_id" => $ticketId
                    ], 200);
                } else {
                    return response()->json(["message" => "Callback request is not resolved yet"], 412);
                }
            } 
            elseif ($ticket->userquery && $ticket->callback_requested) {
                // Check both userquery_resolution_status and callback_request_resolution_status
                if ($ticket->userquery_resolution_status && $ticket->callback_request_resolution_status) {
                    $ticket->is_ticket_resolved = true;
                    $ticket->ticket_resolved = Carbon::now(); // Set current timestamp
                    $ticket->follow_up = null; // Set follow-up to null
                    $ticket->save();

                    return response()->json([
                        "message" => "Ticket resolved successfully",
                        "ticket_id" => $ticketId
                    ], 200);
                } else {
                    return response()->json(["message" => "Either user query or callback request is not resolved yet"], 412);
                }
            }

            // Default case if none of the above conditions are met
            return response()->json(["message" => "No resolution criteria met"], 400);

        } catch (\Exception $e) {
            Log::error("Error: " . $e->getMessage());
            return response()->json(["message" => "Internal server error"], 500);
        }
    }

    public function getResolvedTicketCount()
    {
        try {
            $resolvedTickets = getResolvedTicketCount::where('is_ticket_resolved', true)->count();

            return response()->json([
                "resolved_ticket_count" => $resolvedTickets
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error: " . $e->getMessage());
            return response()->json(["message" => "Internal server error"], 500);
        }
    }

    public function getUnresolvedTicketCount()
    {
        try {
            $unresolvedTickets = getUnresolvedTicketCount::where('is_ticket_resolved', false)->count();

            return response()->json([
                "unresolved_ticket_count" => $unresolvedTickets
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error: " . $e->getMessage());
            return response()->json(["message" => "Internal server error"], 500);
        }
    }

    public function getTicketResolvedTime(Request $request)
    {
        $ticketId = $request->query('ticket_id'); // Get ticket_id from query parameters

        if (!$ticketId) {
            return response()->json(["message" => "Ticket ID is required"], 400);
        }

        $ticket = getTicketResolvedTime::where('ticket_id', $ticketId)->first();

        if (!$ticket) {
            return response()->json(["message" => "Ticket not found"], 404);
        }

        return response()->json([
            "ticket_id" => $ticket->ticket_id,
            "ticket_resolved" => $ticket->ticket_resolved
        ], 200);
    }

    public function getConversationDuration(Request $request)
    {
        $userId = $request->query('user_id'); // Get user_id from query parameters

        if (!$userId) {
            return response()->json(["message" => "User ID is required"], 400);
        }

        $chatbotData = ChatbotData::where('user_id', $userId)->first();

        if (!$chatbotData) {
             return response()->json(["message" => "User data not found"], 404);
        }

        if (!$chatbotData->conv_started || !$chatbotData->conv_ended) {
            return response()->json(["message" => "Conversation start or end time not set"], 400);
        }

        // Convert stored time values to Carbon instances
        $convStarted = Carbon::parse($chatbotData->conv_started);
        $convEnded = Carbon::parse($chatbotData->conv_ended);

        // Calculate the duration
        $durationSeconds = $convEnded->diffInSeconds($convStarted);
        $durationFormatted = gmdate("H:i:s", $durationSeconds); // Format as HH:MM:SS

        return response()->json([
            "user_id" => $userId,
            "conversation_duration" => $durationFormatted
        ], 200);
    }

    public function saveRemarkAndFollowUp(Request $request)
    {
        try {
            $ticketId = $request->input('ticket_id');
            $remark = $request->input('remark');
            $followUpDate = $request->input('follow_up_date'); // Optional

            // Check if ticket exists
            $ticket = saveRemarkAndFollowUp::where('ticket_id', $ticketId)->first();

            if (!$ticket) {
                return response()->json(["message" => "Ticket not found"], 404);
            }

            // Save remark if provided
            if (!empty($remark)) {
                $ticket->agent_remarks = $remark;
            }

            // Save follow-up date if provided
            if (!empty($followUpDate)) {
                // Parse follow-up date string to a proper date format
                $parsedFollowUpDate = Carbon::createFromFormat('Y-m-d', $followUpDate)->toDateString();
                $ticket->follow_up = $parsedFollowUpDate;
            }

            // Save changes
            $ticket->save();

            return response()->json(["message" => "Remark and Follow-up date saved successfully"], 200);

        } catch (\Exception $e) {
            Log::error("An error occurred: " . $e->getMessage());
            return response()->json(["message" => "Internal server error"], 500);
        }
    }













//     public function getRemarks(Request $request)
// {
//     $ticket_id = $request->query('ticket_id');

//     if (!$ticket_id) {
//         return response()->json(["message" => "Ticket ID is required"], 400);
//     }

//     $ticket = getRemarks::where('ticket_id', $ticket_id)->first();

//     if (!$ticket) {
//         return response()->json(["message" => "Ticket not found"], 404);
//     }

//     return response()->json(["remarks" => $ticket->agent_remarks], 200);
// }


public function getRemarks(Request $request)
{
    $ticket_id = $request->query('ticket_id');

    if (!$ticket_id) {
        return response()->json(["message" => "Ticket ID is required"], 400);
    }

    $ticket = getRemarks::where('ticket_id', $ticket_id)->first();

    if (!$ticket) {
        return response()->json(["message" => "Ticket not found"], 404);
    }

    return response()->json([
        "remarks" => $ticket->agent_remarks,
        "follow_up" => $ticket->follow_up, // Added follow_up
    ], 200);
}



public function getFollowUpTickets()
    {
        try {
            // Fetch tickets where follow_up is NOT NULL
            $tickets = getFollowUpTickets::whereNotNull('follow_up')->get();

            if ($tickets->isEmpty()) {
                return response()->json(["message" => "No tickets with follow-up requests found"], 404);
            }

            $ticketList = $tickets->map(function ($ticket) {
                return [
                    "ticket_id" => $ticket->ticket_id,
                    "ticket_title" => $ticket->ticket_title,
                    "updated" => $ticket->ticket_resolved ? $ticket->ticket_resolved->format("Y-m-d H:i:s") : null,
                    "action" => $ticket->is_ticket_resolved ? "Resolved" : "Pending",
                    "remark" => $ticket->agent_remarks ?? "No remark",
                    "follow_up_date" => $ticket->follow_up ? $ticket->follow_up->format("Y-m-d") : null,
                ];
            });

            return response()->json(["tickets" => $ticketList], 200);
        } catch (\Exception $e) {
            Log::error("Error fetching follow-up tickets: " . $e->getMessage());
            return response()->json(["message" => "Internal server error"], 500);
        }
    }

    public function updateTicketResolutionStatus(Request $request)
    {
        try {
            $ticketId = $request->input('ticket_id');
            $resolutionStatus = $request->input('resolution_status');

            // Find the ticket
            $ticket = updateTicketResolution::where('ticket_id', $ticketId)->first();

            if (!$ticket) {
                return response()->json(["message" => "Ticket not found"], 404);
            }

            // Update resolution status
            $ticket->ticket_resolution_status = $resolutionStatus;
            $ticket->save();

            return response()->json([
                "message" => "Ticket resolution status updated successfully",
                "ticket_id" => $ticketId
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error updating ticket resolution status: " . $e->getMessage());
            return response()->json(["message" => "Internal server error"], 500);
        }
    }

    public function getAllStatus()
    {
        $statuses = Status::select('description')->get();

        if ($statuses->isEmpty()) {
            return response()->json(['message' => 'No statuses found'], 404);
        }

        return response()->json(['statuses' => $statuses], 200);
    }

}
