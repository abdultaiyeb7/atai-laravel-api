<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class submitDetails extends Model
{
    protected $table = 'atjoin_chatbot_data'; // Update this if your table name is different

    protected $primaryKey = 'user_id'; // Assuming user_id is the primary key

    public $timestamps = false; // Set to true if you have created_at and updated_at columns

    protected $fillable = [
        'user_id',
        'name',
        'contact',
        'email',
        'session_level',
        'userquery'
    ];
}
