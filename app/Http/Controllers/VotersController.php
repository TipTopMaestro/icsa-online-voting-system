<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class VotersController extends Controller
{
    public function index(Request $request)
    {
        // Get active election from optimized view
        $activeElection = DB::table('view_election_statistics')
            ->where('is_active', 1)
            ->first();

        // Use the optimized view_voter_details view
        $query = DB::table('view_voter_details')
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_id', 'like', "%{$search}%")
                    ->orWhere('course', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
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
        if ($request->has('has_voted') && $request->has_voted !== null) {
            $hasVotedValue = $request->has_voted === 'true' || $request->has_voted === true;
            $query->where('has_voted_active', $hasVotedValue ? 1 : 0);
        }

        $voters = $query->paginate(15)->through(function ($voter) {
            return [
                'id' => $voter->profile_id,
                'user_id' => $voter->user_id,
                'student_id' => $voter->student_id,
                'course' => $voter->course,
                'year_level' => $voter->year_level,
                'section' => $voter->section,
                'has_voted' => (bool)$voter->has_voted_active,
                'created_at' => $voter->created_at,
                'updated_at' => $voter->updated_at,
                'user' => [
                    'id' => $voter->user_id,
                    'name' => $voter->name,
                    'email' => $voter->email,
                    'role' => $voter->role,
                    'photo' => $voter->photo 
                        ? asset('storage/' . $voter->photo)
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
