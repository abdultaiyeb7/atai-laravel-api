<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class terminateResponse extends Model
{

    use HasFactory;

    protected $table = 'atjoin_chatbot_data'; // Replace with your actual table name

    protected $primaryKey = 'user_id'; // Primary key is user_id

    public $incrementing = false; // Since user_id is not auto-incrementing
    protected $keyType = 'string'; // Define key type if user_id is a string

    protected $fillable = [
        'user_id',
        'name',
        'contact',
        'email',
        'callback_requested',
        'userquery',
        'is_terminated',
        'conv_ended'
    ];

    public $timestamps = false; // Disable timestamps if not using created_at and updated_at
}