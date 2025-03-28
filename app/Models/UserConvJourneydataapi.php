<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;



class UserConvJourneydataapi extends Model
{

    use HasFactory;

    protected $table = 'user_conv_journey'; // Adjust table name if needed
    protected $primaryKey = 'user_conv_journey_id';
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_conv_journey_id',
        'user_conversation'
    ];

    
}