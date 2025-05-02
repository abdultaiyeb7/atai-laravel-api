<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class getFollowUpTickets extends Model

{
    use HasFactory;

    protected $table = 'tickets_data';
    protected $primaryKey = 'ticket_id'; // Assuming ticket_id is the primary key
    public $incrementing = false; // Disable auto-increment if it's not an integer
    protected $keyType = 'string'; // Specify primary key as string

    protected $fillable = [
        'ticket_id', 'ticket_title', 'ticket_resolved', 
        'is_ticket_resolved', 'agent_remarks', 'follow_up'
    ];

    protected $casts = [
        'ticket_resolved' => 'datetime',
        'follow_up' => 'date'
    ];
}
