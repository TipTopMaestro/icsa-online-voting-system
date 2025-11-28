<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Election extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_datetime',
        'end_datetime',
        'is_active',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    public function candidates()
    {
        return $this->hasManyThrough(Candidate::class, Position::class, 'election_id', 'position_id', 'id', 'id');
    }

    public function votes()
    {
        return $this->hasManyThrough(Vote::class, Position::class);
    }

    public function totalVotersCount()
    {
        return VoterProfile::count();
    }

    public function isActive()
    {
        return $this->is_active && 
               now()->between($this->start_datetime, $this->end_datetime);
    }

    public function hasStarted()
    {
        return $this->start_datetime && now()->greaterThanOrEqualTo($this->start_datetime);
    }

    public function hasEnded()
    {
        return $this->end_datetime && now()->greaterThan($this->end_datetime);
    }
}
