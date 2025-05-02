<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class getTicketResolvedTime extends Model
{
    use HasFactory;

    protected $table = 'tickets_data';

    protected $fillable = [
        'ticket_id',
        'ticket_resolved'
    ];
}