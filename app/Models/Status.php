<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Status extends Model
{
    use HasFactory;

    protected $table = 'status'; // Table name
    protected $primaryKey = 'id'; // Primary Key
    public $timestamps = false; // Disable timestamps

    protected $fillable = ['description']; // Allowed fields
}