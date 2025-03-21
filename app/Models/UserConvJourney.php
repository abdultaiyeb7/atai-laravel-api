<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserConvJourney extends Model
{
    use HasFactory;

    protected $table = 'user_conv_journey'; // Ensure this matches your table name

    protected $fillable = [
        'user_conv_journey_id',
        'user_conversation'
    ];
}
