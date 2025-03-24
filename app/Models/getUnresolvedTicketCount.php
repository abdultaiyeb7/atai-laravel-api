<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class getUnresolvedTicketCount extends Model
{
    use HasFactory;

    protected $table = 'tickets_data';

    protected $fillable = [
        'ticket_id',
        'is_ticket_resolved'
    ];
}