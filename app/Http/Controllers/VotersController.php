<?php

namespace App\Http\Controllers;

use App\Models\VoterProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VotersController extends Controller
{
    public function index(Request $request)
    {
        // Debug logging
        \Log::info('Voter Search Request', [
            'search' => $request->search,
            'course' => $request->course,
            'year_level' => $request->year_level,
            'has_voted' => $request->has_voted,
        ]);

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

        // Filter by voting status
        if ($request->has('has_voted') && $request->has_voted !== null) {
            $query->where('has_voted', $request->has_voted === 'true' || $request->has_voted === true);
        }

        $voters = $query->paginate(15);

        \Log::info('Voter Query Result', [
            'total' => $voters->total(),
            'count' => $voters->count(),
        ]);

        return Inertia::render('admin/voters', [
            'voters' => $voters,
            'filters' => $request->only(['search', 'course', 'year_level', 'has_voted']),
        ]);
    }
}
