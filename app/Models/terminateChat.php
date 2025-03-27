<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class terminateChat extends Model
{
    protected $table = 'atjoin_chatbot_data'; // Replace with your actual table name

    protected $fillable = [
        'user_id',
        'session_level',
        // Add other fillable fields as needed
    ];

    public $timestamps = false; // Set to true if your table has 'created_at' and 'updated_at' columns
}