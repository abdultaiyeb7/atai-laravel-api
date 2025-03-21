<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class UserInfo extends Model
{
    use HasFactory;

    protected $table = 'tickets_data'; // Ensure this matches your database table

    protected $fillable = [
        'ticket_id',
        'user_id',
        'user_name',
        'email',
        'contact'
    ];
}