<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatbotDataapi extends Model
{
    use HasFactory;

    protected $table = 'atjoin_chatbot_data'; // Your table name
    protected $primaryKey = 'user_id';
    public $incrementing = false; // Assuming user_id is manually assigned
    protected $keyType = 'string'; // Allow alphanumeric user IDs
    // protected $fillable = ['user_id', 'name', 'email', 'contact', 'callback_requested', 'session_level'];
    protected $fillable = ['user_id', 'name', 'contact', 'email', 'userquery', 'session_level', 'visitor_type'];

    public $timestamps = false; // Disable automatic timestamps
}
