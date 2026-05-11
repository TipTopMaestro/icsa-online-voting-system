<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Election;
use App\Models\VoterProfile;
use Illuminate\Support\Facades\DB;


class ElectionController extends Controller
{
    public function election() {
        // Fetch pre-calculated statistics via Stored Procedure
        $elections = collect(DB::select('CALL sp_GetElectionStatistics()'));

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

        // Call the stored procedure
        // Default is_active to 0 (false) on creation
        DB::statement('CALL sp_CreateElection(?, ?, ?, ?, ?)', [
            $validated['title'],
            $validated['description'],
            $validated['start_datetime'],
            $validated['end_datetime'],
            0// p_is_active
        ]);

        return redirect()->back()->with('success', 'Election created successfully');
    } catch (\Exception $e) {
        \Log::error('Election creation error: ' . $e->getMessage());
        return redirect()->back()->withErrors(['error' => 'Failed to create election.']);
    }
}


    public function update(Request $request, Election $election) {
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'start_datetime' => 'required|date',
        'end_datetime' => 'required|date|after:start_datetime',
    ]);

    try {
        // Call the stored procedure sp_UpdateElection
        DB::statement('CALL sp_UpdateElection(?, ?, ?, ?, ?)', [
            $election->id,
            $validated['title'],
            $validated['description'],
            $validated['start_datetime'],
            $validated['end_datetime']
        ]);

        return redirect()->back()->with('success', 'Election updated successfully');
    } catch (\Exception $e) {
        \Log::error('Election update error: ' . $e->getMessage());
        return redirect()->back()->withErrors(['error' => 'Failed to update election.']);
    }
}


    public function destroy(Election $election) {
    try {
        // Call the stored procedure sp_DeleteElection
        DB::statement('CALL sp_DeleteElection(?)', [$election->id]);

        return redirect()->back()->with('success', 'Election deleted successfully');
    } catch (\Exception $e) {
        \Log::error('Election deletion error: ' . $e->getMessage());
        
        // If there is a foreign key restriction (e.g. archiving required), 
        // the error will be caught here.
        return redirect()->back()->withErrors([
            'error' => 'Failed to delete election. It may have active records tied to it.'
        ]);
    }
}


    public function activate(Election $election) {
        try {
            // Call the stored procedure sp_ActivateElection
            DB::statement('CALL sp_ActivateElection(?)', [$election->id]);

            return redirect()->back()->with('success', 'Election activated successfully');
        } catch (\Exception $e) {
            \Log::error('Election activation error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to activate election.']);
        }
    }

    public function deactivate(Election $election) {
        try {
            // Call the stored procedure sp_DeactivateElection
            DB::statement('CALL sp_DeactivateElection(?)', [$election->id]);

            return redirect()->back()->with('success', 'Election ended successfully');
        } catch (\Exception $e) {
            \Log::error('Election deactivation error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to end election.']);
        }
    }

    private function hasStarted($election) {
        return $election->start_datetime && now()->greaterThanOrEqualTo($election->start_datetime);
    }

    private function getElectionStatus($election) {
        if (!$election->start_datetime || !$election->end_datetime) {
            return 'scheduled';
        }

        $now = now();

        // If manually activated, show as active regardless of schedule
        if ($election->is_active) {
            return 'active';
        }

        if ($now->greaterThan($election->end_datetime)) {
            return 'ended';
        }

        if ($now->lessThan($election->start_datetime)) {
            return 'scheduled';
        }

        return 'scheduled';
    }
}
