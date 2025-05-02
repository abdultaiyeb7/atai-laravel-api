<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class submitCallbackPreference extends Model
{
    protected $table = 'atjoin_chatbot_data'; // Update the table name as per your database
    protected $primaryKey = 'user_id'; // Assuming 'user_id' is the primary key
    public $timestamps = false; // Set to true if you have created_at and updated_at fields

    protected $fillable = ['user_id', 'callback_requested', 'session_level'];
}