<?php

namespace App\Http\Controllers;

use App\Models\VoterProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VotersController extends Controller
{
    public function index(Request $request)
    {
        // Get active election - use isActive() method that checks both is_active and date range
        $activeElection = \App\Models\Election::where('is_active', true)
            ->get()
            ->first(function ($election) {
                return $election->isActive();
            });

        $query = VoterProfile::with('user')
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_id', 'like', "%{$search}%")
                    ->orWhere('course', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by course
        if ($request->has('course') && $request->course) {
            $query->where('course', $request->course);
        }

        // Filter by year level
        if ($request->has('year_level') && $request->year_level) {
            $query->where('year_level', $request->year_level);
        }

        // Filter by voting status (for active election)
        if ($request->has('has_voted') && $request->has_voted !== null && $activeElection) {
            $hasVotedValue = $request->has_voted === 'true' || $request->has_voted === true;
            
            if ($hasVotedValue) {
                // Show only voters who voted in active election
                $query->whereHas('user', function($q) use ($activeElection) {
                    $q->whereHas('votes', function($vq) use ($activeElection) {
                        $vq->where('election_id', $activeElection->id);
                    });
                });
            } else {
                // Show only voters who haven't voted in active election
                $query->whereHas('user', function($q) use ($activeElection) {
                    $q->whereDoesntHave('votes', function($vq) use ($activeElection) {
                        $vq->where('election_id', $activeElection->id);
                    });
                });
            }
        }

        $voters = $query->paginate(15)->through(function ($voter) use ($activeElection) {
            // Check if voter has voted in active election
            $hasVotedInActiveElection = false;
            if ($activeElection) {
                $hasVotedInActiveElection = \App\Models\Vote::where('user_id', $voter->user_id)
                    ->where('election_id', $activeElection->id)
                    ->exists();
            }
            
            return [
                'id' => $voter->id,
                'user_id' => $voter->user_id,
                'student_id' => $voter->student_id,
                'course' => $voter->course,
                'year_level' => $voter->year_level,
                'section' => $voter->section,
                'has_voted' => $hasVotedInActiveElection, // Per active election
                'created_at' => $voter->created_at,
                'updated_at' => $voter->updated_at,
                'user' => [
                    'id' => $voter->user->id,
                    'name' => $voter->user->name,
                    'email' => $voter->user->email,
                    'role' => $voter->user->role,
                    'email_verified_at' => $voter->user->email_verified_at,
                    'photo' => $voter->user->photo 
                        ? asset('storage/' . $voter->user->photo)
                        : null,
                ],
            ];
        });

        return Inertia::render('admin/voters', [
            'voters' => $voters,
            'filters' => $request->only(['search', 'course', 'year_level', 'has_voted']),
            'activeElection' => $activeElection ? [
                'id' => $activeElection->id,
                'title' => $activeElection->title,
            ] : null,
        ]);
    }
}
