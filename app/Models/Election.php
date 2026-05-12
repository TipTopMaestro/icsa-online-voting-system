<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Election extends Model
{
    use HasFactory;

    // protected $casts = [
    //     'start_datetime' => 'datetime',
    //     'end_datetime' => 'datetime',
    //     'is_active' => 'boolean',
    // ];

    // public function positions()
    // {
    //     return $this->hasMany(Position::class);
    // }

    // public function candidates()
    // {
    //     return $this->hasMany(Candidate::class);
    // }

    // public function votes()
    // {
    //     return $this->hasManyThrough(Vote::class, Candidate::class);
    // }

    // public function isActive()
    // {
    //     return $this->is_active;
// }
}
