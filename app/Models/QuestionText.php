<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class QuestionText extends Model
{
    use HasFactory;

    protected $table = 'questions'; // Replace if your table name is different

    protected $fillable = [
        'question_text',
        // other fields as needed
    ];
}