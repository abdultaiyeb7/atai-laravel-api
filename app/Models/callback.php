<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class callback extends Model
{
    use HasFactory;

    protected $table = 'tickets_data'; // Ensure this matches your database table

    protected $fillable = [
        'ticket_id',
        'user_name',
        'contact',
        'email',
        'userquery',
        'callback_requested',
        'ticket_resolution_status'
    ];
}
