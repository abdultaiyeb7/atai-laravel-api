<?php
<<<<<<< HEAD
=======

>>>>>>> 12f06b7fe3a132440ea8ea03b4f1820664dc5667
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInquiry extends Model
<<<<<<< HEAD
{
    use HasFactory;

    protected $table   = 'inquiry';
    public $timestamps = true;
    // Add this property to ensure created_at and updated_at are treated as dates
    protected $dates = ['created_at', 'updated_at'];
=======
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
>>>>>>> 12f06b7fe3a132440ea8ea03b4f1820664dc5667

    protected $fillable = [
        'id',
        'user_id',
        'client_name',
        'contact',
        'email',
<<<<<<< HEAD
        'last_question',
        'created_at', // Add created_at
        'updated_at', // Add updated_at
=======
        'last_question'
>>>>>>> 12f06b7fe3a132440ea8ea03b4f1820664dc5667
    ];

    // Add this relationship
    public function lastQuestion()
    {
        return $this->belongsTo(Question::class, 'last_question');
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> 12f06b7fe3a132440ea8ea03b4f1820664dc5667
