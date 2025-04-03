<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class duration extends Model
{
    use HasFactory;

    protected $table = 'atjoin_chatbot_data';

    protected $fillable = [
        'user_id',
        'conv_started',
        'conv_ended'
    ];
}