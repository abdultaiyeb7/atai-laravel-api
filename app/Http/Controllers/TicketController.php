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

}
