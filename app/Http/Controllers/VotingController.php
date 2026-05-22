<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class VotingController extends Controller
{
    /**
     * Show voting page with active election and candidates
     */
    public function index()
    {
        $user = Auth::user();
        
        // Fetch the active election from optimized view
        $election = DB::table('view_election_statistics')
            ->where('is_active', 1)
            ->first();

        // No active election
        if (!$election) {
            return Inertia::render('voter/vote', [
                'election' => null,
                'positions' => [],
                'hasVoted' => false,
                'message' => 'No active election at the moment.'
            ]);
        }

        // Check if user has already voted in this election
        $hasVoted = DB::table('votes')->where('user_id', $user->id)
            ->where('election_id', $election->id)
            ->exists();

        if ($hasVoted) {
            // Show message on vote page instead of redirect
            return Inertia::render('voter/vote', [
                'election' => $election,
                'positions' => [],
                'hasVoted' => true,
                'message' => "You have already voted in {$election->title}. Thank you for participating!"
            ]);
        }

        // Get all candidates for the active election using optimized view
        $allCandidates = DB::table('view_candidates_details')
            ->where('election_id', $election->id)
            ->get();

        // Get positions for this election
        $positions = DB::table('positions')
            ->where('election_id', $election->id)
            ->orderBy('id')
            ->get()
            ->map(function ($position) use ($allCandidates) {
                $position->candidates = $allCandidates->where('position_id', $position->id)
                    ->map(function ($candidate) {
                        return [
                            'id' => $candidate->id,
                            'user_id' => $candidate->user_id,
                            'position_id' => $candidate->position_id,
                            'name' => $candidate->user_name,
                            'email' => $candidate->user_email,
                            'partylist' => $candidate->partylist,
                            'platform' => $candidate->platform,
                            'photo' => $candidate->photo,
                            'course' => $candidate->course,
                            'year_level' => $candidate->year_level,
                            'section' => $candidate->section,
                            'user' => (object)[
                                'name' => $candidate->user_name,
                                'email' => $candidate->user_email
                            ]
                        ];
                    })->values();
                return $position;
            });

        return Inertia::render('voter/vote', [
            'election' => $election,
            'positions' => $positions,
            'hasVoted' => $hasVoted
        ]);
    }

    /**
     * Store votes (submit ballot) using stored procedure
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Validate request - using DB table for existence checks
        $validated = $request->validate([
            'election_id' => 'required|exists:elections,id',
            'votes' => 'required|array|min:1',
            'votes.*.position_id' => 'required|exists:positions,id',
            'votes.*.candidate_ids' => 'required|array|min:1',
            'votes.*.candidate_ids.*' => 'required|exists:candidates,id',
        ]);

        try {
            // Call the stored procedure sp_CastBallot
            // We pass the votes array as a JSON string
            DB::statement('CALL sp_CastBallot(?, ?, ?)', [
                $user->id,
                $validated['election_id'],
                json_encode($validated['votes'])
            ]);

            // Redirect to receipt page
            return redirect()->route('voter.receipt', [
                'election_id' => $validated['election_id']
            ])->with('success', 'Your vote has been submitted successfully!');

        } catch (\PDOException $e) {
            // The stored procedure raises errors using SIGNAL SQLSTATE '45000'
            \Log::error('Vote submission via procedure failed', [
                'user_id' => $user->id,
                'election_id' => $validated['election_id'],
                'error' => $e->getMessage()
            ]);
            
            $errorMessage = 'Failed to submit votes. Please try again.';
            
            // Extract custom message from SQL error if possible
            if ($e->getCode() == '45000') {
                $errorMessage = $e->getMessage();
            }

            return redirect()->back()->withErrors([
                'error' => $errorMessage
            ]);
        } catch (\Exception $e) {
            \Log::error('Unexpected error during vote submission', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->withErrors([
                'error' => 'An unexpected error occurred.'
            ]);
        }
    }

    /**
     * Show voting receipt/confirmation page
     */
    public function receipt(Request $request)
    {
        $user = Auth::user();
        $electionId = $request->election_id;

        // Get votes cast by user in this election via optimized view
        $votes = DB::table('view_voting_receipt')
            ->where('user_id', $user->id)
            ->where('election_id', $electionId)
            ->get()
            ->map(function($vote) {
                 // Compatibility mapping for receipt UI
                 $vote->candidate = (object)[
                     'name' => $vote->candidate_name,
                     'partylist' => $vote->partylist,
                     'photo' => $vote->candidate_photo,
                     'position' => (object)['name' => $vote->position_name],
                     'user' => (object)['name' => $vote->candidate_name]
                 ];
                 return $vote;
            });

        if ($votes->isEmpty()) {
            return redirect()->route('voter.dashboard')
                ->with('error', 'No voting record found.');
        }

        $election = DB::table('elections')->where('id', $electionId)->first();

        return Inertia::render('voter/receipt', [
            'election' => $election,
            'votes' => $votes,
            'votedAt' => $votes->first()->created_at
        ]);
    }

    /**
     * Show all candidates for the active election
     */
    public function viewCandidates()
    {
        // Fetch the active election from optimized view
        $election = DB::table('view_election_statistics')
            ->where('is_active', 1)
            ->first();

        // No active election
        if (!$election) {
            return Inertia::render('voter/viewCandidates', [
                'election' => null,
                'candidates' => [],
                'positions' => [],
                'message' => 'No active election at the moment. Please check back later.'
            ]);
        }

        // Get all candidates for this election using the optimized candidates view
        $candidates = DB::table('view_candidates_details')
            ->where('election_id', $election->id)
            ->get()
            ->map(function($candidate) {
                return [
                    'id' => $candidate->id,
                    'name' => $candidate->user_name,
                    'email' => $candidate->user_email,
                    'position' => $candidate->position_name,
                    'position_id' => $candidate->position_id,
                    'party' => $candidate->partylist,
                    'platform' => $candidate->platform,
                    'image' => $candidate->photo ? '/storage/candidates/' . $candidate->photo : null,
                    'course' => $candidate->course . ' ' . $candidate->year_level . $candidate->section,
                    'quote' => $candidate->platform, // Mapped platform to quote as fallback
                ];
            });

        // Get unique positions for filter
        $positions = DB::table('positions')
            ->where('election_id', $election->id)
            ->orderBy('id')
            ->get()
            ->map(function($position) {
                return [
                    'value' => $position->name,
                    'label' => $position->name
                ];
            });

        return Inertia::render('voter/viewCandidates', [
            'election' => $election,
            'candidates' => $candidates,
            'positions' => $positions,
            'message' => null
        ]);
    }
}
