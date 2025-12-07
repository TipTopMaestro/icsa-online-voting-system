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
        return $this->hasMany(Candidate::class);
    }

    public function votes()
    {
        return $this->hasManyThrough(Vote::class, Candidate::class);
    }

    public function totalVotersCount()
    {
        return VoterProfile::count();
    }

    public function isActive()
    {
        // If manually activated, consider it active regardless of schedule
        return $this->is_active;
    }

    public function hasStarted()
    {
        return $this->start_datetime && now()->greaterThanOrEqualTo($this->start_datetime);
    }

    public function hasEnded()
    {
        return $this->end_datetime && now()->greaterThan($this->end_datetime);
    }

    // Additional helper methods for voting system
    public function getTimeRemainingAttribute()
    {
        if ($this->hasEnded()) {
            return null;
        }
        
        return $this->end_datetime->diffForHumans();
    }

    public function getTotalVotersAttribute()
    {
        return VoterProfile::count();
    }

    public function getVotedCountAttribute()
    {
        return Vote::where('election_id', $this->id)
            ->distinct('user_id')
            ->count('user_id');
    }

    public function getTurnoutPercentageAttribute()
    {
        $total = $this->total_voters;
        if ($total == 0) return 0;
        
        return round(($this->voted_count / $total) * 100, 2);
    }

    public function getStatusAttribute()
    {
        if ($this->hasEnded()) {
            return 'ended';
        } elseif ($this->hasStarted() && $this->is_active) {
            return 'active';
        } elseif ($this->hasStarted()) {
            return 'ended';
        } else {
            return 'scheduled';
        }
    }

    protected $appends = ['status'];
}
