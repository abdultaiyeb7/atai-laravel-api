<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;



class UserConvJourneydataapi extends Model
{
    use HasFactory;

    protected $table = 'user_conv_journey'; // Ensure the correct table name
    protected $primaryKey = 'user_conv_journey_id';
    public $incrementing = false; // Because we're manually assigning an alphanumeric ID
    protected $keyType = 'string'; // This allows alphanumeric primary keys
    protected $fillable = ['user_conv_journey_id', 'user_conversation'];

    public $timestamps = false; // Disable timestamps since they are not in your table
}
