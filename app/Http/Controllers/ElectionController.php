<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Election;
use Illuminate\Support\Facades\DB;

class ElectionController extends Controller
{
    public function election() {
        $elections = Election::withCount(['positions', 'candidates', 'votes'])
            ->with('positions')
            ->latest()
            ->get()
            ->map(function ($election) {
                // Count unique voters who voted in this election
                $uniqueVoters = \App\Models\Vote::where('election_id', $election->id)
                    ->distinct('user_id')
                    ->count('user_id');
                
                return [
                    'id' => $election->id,
                    'title' => $election->title,
                    'description' => $election->description,
                    'start_datetime' => $election->start_datetime,
                    'end_datetime' => $election->end_datetime,
                    'is_active' => $election->is_active,
                    'status' => $this->getElectionStatus($election),
                    'positions_count' => $election->positions_count,
                    'candidates_count' => $election->candidates_count,
                    'votes_count' => $election->votes_count,
                    'voted_count' => $uniqueVoters, // Unique voters who voted
                    'total_voters' => $election->totalVotersCount(),
                    'created_at' => $election->created_at,
                    'updated_at' => $election->updated_at,
                ];
            });

        return Inertia::render('admin/election', [
            'elections' => $elections
        ]);
    }

    public function store(Request $request) {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_datetime' => 'required|date',
                'end_datetime' => 'required|date|after:start_datetime',
            ]);

            $election = Election::create($validated);

            return redirect()->back()->with('success', 'Election created successfully');
        } catch (\Exception $e) {
            \Log::error('Election creation error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, Election $election) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
        ]);

        $election->update($validated);

        return redirect()->back()->with('success', 'Election updated successfully');
    }

    public function destroy(Election $election) {
        $election->delete();

        return redirect()->back()->with('success', 'Election deleted successfully');
    }

    public function activate(Election $election) {
        DB::transaction(function () use ($election) {
            Election::where('is_active', true)->update(['is_active' => false]);
            $election->update(['is_active' => true]);
        });

        return redirect()->back()->with('success', 'Election activated successfully');
    }

    public function deactivate(Election $election) {
        $election->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Election ended successfully');
    }

    private function hasStarted($election) {
        return $election->start_datetime && now()->greaterThanOrEqualTo($election->start_datetime);
    }

    private function getElectionStatus($election) {
        if (!$election->start_datetime || !$election->end_datetime) {
            return 'scheduled';
        }

        $now = now();

        if ($now->lessThan($election->start_datetime)) {
            return 'scheduled';
        }

        if ($now->greaterThan($election->end_datetime)) {
            return 'ended';
        }

        if ($election->is_active && $now->between($election->start_datetime, $election->end_datetime)) {
            return 'active';
        }

        return 'scheduled';
    }
}
