<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotDataapi extends Model
{
    protected $table = 'atjoin_chatbot_data'; // Adjust table name if needed
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'name',
        'conv_started',
        'user_conv_journey_id'
    ];
}
