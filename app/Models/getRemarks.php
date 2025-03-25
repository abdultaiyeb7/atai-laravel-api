<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class getRemarks extends Model
{
    use HasFactory;

    protected $table = 'tickets_data';
    protected $primaryKey = 'ticket_id'; // Explicitly define primary key
    public $incrementing = false; // Disable auto-incrementing
    protected $keyType = 'string'; // Treat ticket_id as a string

    protected $fillable = ['ticket_id', 'agent_remarks', 'follow_up']; // Added follow_up

    public $timestamps = false; // Disable timestamps if they don't exist in the table
}
