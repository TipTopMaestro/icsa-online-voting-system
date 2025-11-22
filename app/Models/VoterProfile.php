<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class VoterProfile extends Model
{
    protected $fillable = [
        'user_id',
        'student_id',
        'course',
        'year_level',
        'section',
        'has_voted',
    ];
}
