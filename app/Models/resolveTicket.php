<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class resolveTicket extends Model
{
    use HasFactory;

    protected $table = 'tickets_data'; // Ensure this matches your database table

    protected $primaryKey = 'ticket_id'; // Set correct primary key
    public $incrementing = false; // If ticket_id is not auto-incremented
    protected $keyType = 'string'; // Change to 'integer' if ticket_id is numeric

    public $timestamps = false; // Disable Laravel's automatic timestamps

    protected $fillable = [
        'ticket_id',
        'userquery',
        'userquery_resolution_status',
        'callback_requested',
        'callback_request_resolution_status',
        'is_ticket_resolved',
        'ticket_resolved',
        'follow_up'
    ];
}
