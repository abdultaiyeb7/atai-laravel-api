<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class saveRemarkAndFollowUp extends Model
{
    use HasFactory;

    protected $table = 'tickets_data';
    protected $primaryKey = 'ticket_id'; // Define primary key explicitly
    public $incrementing = false; // Disable auto-incrementing
    protected $keyType = 'string'; // Set key type to string if it's not an integer

    protected $fillable = [
        'ticket_id',
        'agent_remarks',
        'follow_up'
    ];

    public $timestamps = false; // If your table does not have created_at/updated_at
}