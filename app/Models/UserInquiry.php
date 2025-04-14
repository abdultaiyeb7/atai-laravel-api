<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInquiry extends Model
{
    use HasFactory;

    protected $table = 'inquiry'; // Replace with your actual table name if different

    protected $fillable = [
        'user_id',
        'client_name',
        'contact',
        'email'
        // Add other fields as needed
    ];
}