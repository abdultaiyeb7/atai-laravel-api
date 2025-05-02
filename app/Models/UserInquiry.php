<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInquiry extends Model
{
    use HasFactory;

    protected $table   = 'inquiry';
    public $timestamps = true;
    // Add this property to ensure created_at and updated_at are treated as dates
    protected $dates = ['created_at', 'updated_at'];

    protected $fillable = [
        'id',
        'user_id',
        'client_name',
        'contact',
        'email',
        'last_question',
        'created_at', // Add created_at
        'updated_at', // Add updated_at
    ];

    // Add this relationship
    public function lastQuestion()
    {
        return $this->belongsTo(Question::class, 'last_question');
    }
}
