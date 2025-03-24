<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class updateTicketResolution extends Model
{
    use HasFactory;

    protected $table = 'tickets_data';
    protected $primaryKey = 'ticket_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false; // ✅ Disable timestamps

    protected $fillable = [
        'ticket_id', 'ticket_resolution_status'
    ];
}