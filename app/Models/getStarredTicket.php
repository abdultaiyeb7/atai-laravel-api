<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class getStarredTicket extends Model
{
    use HasFactory;

    protected $table = 'tickets_data'; // Ensure this matches your database table

    protected $primaryKey = 'ticket_id'; // Ensure Laravel uses 'ticket_id' instead of 'id'
    public $incrementing = false; // If 'ticket_id' is not auto-incremented
    protected $keyType = 'string'; // Change to 'integer' if 'ticket_id' is numeric

    protected $fillable = [
        'ticket_id',
        'ticket_starred'
    ];
}
