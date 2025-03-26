<?php

namespace App\Models\ChatbotAI;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatbotData extends Model
{
    use HasFactory;

    protected $table = 'atjoin_chatbot_data'; // Your table name
    protected $primaryKey = 'user_id';
    public $incrementing = false; // Assuming user_id is manually assigned
    protected $keyType = 'string'; // Allow alphanumeric user IDs
    protected $fillable = ['user_id', 'name', 'email', 'contact', 'callback_requested', 'session_level'];

    public $timestamps = false; // Disable automatic timestamps
}
