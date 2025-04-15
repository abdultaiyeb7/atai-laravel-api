<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInquiry extends Model
// {
//     use HasFactory;

//     protected $table = 'inquiry'; // Replace with your actual table name if different

//     protected $fillable = [
//         'user_id',
//         'client_name',
//         'contact',
//         'email'
//         // Add other fields as needed
//     ];
// }

{
    use HasFactory;

    protected $table = 'inquiry';

    protected $fillable = [
        'id',
        'user_id',
        'client_name',
        'contact',
        'email',
        'last_question'
    ];

    // Add this relationship
    public function lastQuestion()
    {
        return $this->belongsTo(Question::class, 'last_question');
    }
}