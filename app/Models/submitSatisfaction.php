<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class submitSatisfaction extends Model
{
    protected $table = 'atjoin_chatbot_data'; // Your table name

    protected $primaryKey = 'user_id'; // Define user_id as the primary key

    public $incrementing = false; // Since user_id is not auto-incrementing

    protected $keyType = 'string'; // Define the key type if user_id is a string

    protected $fillable = [
        'user_id',
        'satisfaction_level',
        // Add other fillable fields if needed
    ];

    public $timestamps = false; // Set to true if your table has timestamps
}
