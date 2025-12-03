<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoterProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_id',
        'course',
        'year_level',
        'section',
        'has_voted',
    ];

    protected $casts = [
        'has_voted' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper Methods
    public function hasVoted(): bool
    {
        return $this->has_voted;
    }

    public function canVote(): bool
    {
        return !$this->has_voted && $this->user->email_verified_at !== null;
    }

    public function markAsVoted(): void
    {
        $this->update(['has_voted' => true]);
    }

    public function getFullInfoAttribute(): string
    {
        return "{$this->student_id} - {$this->course} {$this->year_level}{$this->section}";
    }
}
