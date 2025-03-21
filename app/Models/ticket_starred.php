<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ticket_starred extends Model
{
    use HasFactory;

    protected $table = 'tickets_data'; // Ensure this matches your actual table name
    protected $primaryKey = 'ticket_id'; // Define primary key
    public $timestamps = false; // Disable automatic timestamps

    protected $fillable = [
        'ticket_id',
        'ticket_starred'
    ];
}