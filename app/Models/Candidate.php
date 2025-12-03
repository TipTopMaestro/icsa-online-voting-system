<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'election_id',
        'position_id',
        'partylist',
        'platform',
        'photo',
        'course',
        'year_level',
        'section',
        'votes_count',
    ];

    protected $casts = [
        'votes_count' => 'integer',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    // Helper Methods
    public function getPhotoUrlAttribute(): string
    {
        return asset('storage/candidates/' . $this->photo);
    }

    public function getFullInfoAttribute(): string
    {
        return "{$this->user->name} - {$this->position->name} ({$this->election->title})";
    }

    public function incrementVotes(): void
    {
        $this->increment('votes_count');
    }

    // Scopes
    public function scopeForElection($query, $electionId)
    {
        return $query->where('election_id', $electionId);
    }

    public function scopeForPosition($query, $positionId)
    {
        return $query->where('position_id', $positionId);
    }

    public function scopeWithPartylist($query, $partylist)
    {
        return $query->where('partylist', $partylist);
    }
}

