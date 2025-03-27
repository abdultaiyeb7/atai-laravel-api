<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class terminateResponse extends Model
{
    protected $table = 'atjoin_chatbot_data'; // Replace with your actual table name

    protected $fillable = [
        'user_id',
        'name',
        'contact',
        'email',
        'callback_requested',
        'userquery',
        'is_terminated',
        // Add other fillable fields as needed
    ];

    public $timestamps = false; // Set to true if your table has 'created_at' and 'updated_at' columns
}