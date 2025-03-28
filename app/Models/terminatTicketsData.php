<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class terminatTicketsData extends Model
{
    use HasFactory;

    protected $table = 'tickets_data'; // Set your actual table name

    protected $primaryKey = 'ticket_id'; // Define primary key

    public $incrementing = false;
    protected $keyType = 'string';

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
        'ticket_resolution_status'
    ];

    public $timestamps = false;
}