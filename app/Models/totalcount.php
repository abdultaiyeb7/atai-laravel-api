<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class totalcount extends Model
{
    use HasFactory;

    protected $table = 'tickets_data'; // Ensure this matches your database table
}