<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketsData extends Model
{
    use HasFactory;

    protected $table = 'tickets_data';

    protected $fillable = [
        'ticket_id',
        'ticket_title',
        'ticket_created',
        'ticket_resolved',
        'ticket_resolution_status',
        'agent_remarks' // Ensure this field is included
    ];
}
