<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovedStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'name',
        'email',
        'course',
        'year_level',
        'section',
    ];

    // Helper Methods
    public function isRegistered(): bool
    {
        return VoterProfile::where('student_id', $this->student_id)->exists();
    }

    public function getVoterProfile(): ?VoterProfile
    {
        return VoterProfile::where('student_id', $this->student_id)->first();
    }

    public function getFullInfoAttribute(): string
    {
        return "{$this->student_id} - {$this->name} ({$this->course} {$this->year_level}{$this->section})";
    }

    public function scopeNotRegistered($query)
    {
        $registeredStudentIds = VoterProfile::pluck('student_id');
        return $query->whereNotIn('student_id', $registeredStudentIds);
    }
}
