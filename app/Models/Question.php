<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $table = 'questions';
    protected $primaryKey = 'id';
    protected $fillable = [
        'question_text',
        'question_label',
        'question_type',
        'client_id',
        'question_level',
        'question_parent_level'
    ];

    // Disable timestamps
    public $timestamps = false;

    // Define constants for question types
    const TYPE_TEXT = 1;
    const TYPE_FILE_DOWNLOAD = 2;
    const TYPE_REDIRECT_LINK = 3;
    const TYPE_VIDEO = 4;
    const TYPE_IMAGE = 5;
    const TYPE_LINK = 6;

    /**
     * Relationship to fetch child questions for a specific client
     */
    public function children()
{
    return $this->hasMany(Question::class, 'question_parent_level', 'question_level');
}

}
