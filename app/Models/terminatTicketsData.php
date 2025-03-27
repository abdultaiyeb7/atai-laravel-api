<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class terminatTicketsData extends Model
{
    protected $table = 'tickets_data'; // Replace with your actual table name

    protected $fillable = [
        'ticket_id',
        'user_id',
        'user_name',
        'contact',
        'email',
        'callback_requested',
        'userquery',
        'user_conv_journey_id',
        'is_ticket_resolved',
        'ticket_starred',
        // Add other fillable fields as needed
    ];

    public $timestamps = true; // Assuming you have 'created_at' and 'updated_at' columns
}