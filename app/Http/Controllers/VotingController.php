<?php

namespace App\Http\Controllers;

use App\Models\Election;
use App\Models\Position;
use App\Models\Vote;
use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VotingController extends Controller
{
    /**
     * Show voting page with active election and candidates
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get active election - use isActive() method
        $election = Election::where('is_active', true)
            ->get()
            ->first(function ($e) {
                return $e->isActive();
            });

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
        $hasVoted = Vote::where('user_id', $user->id)
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

        // Get positions with candidates
        $positions = Position::where('election_id', $election->id)
            ->with(['candidates.user'])
            ->orderBy('id')
            ->get();

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
        
        // Validate request - keep this for immediate UI feedback
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
            // We catch these and return them to the user
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

        // Get votes cast by user in this election
        $votes = Vote::where('user_id', $user->id)
            ->where('election_id', $electionId)
            ->with(['candidate.user', 'candidate.position', 'candidate'])
            ->get();

        if ($votes->isEmpty()) {
            return redirect()->route('voter.dashboard')
                ->with('error', 'No voting record found.');
        }

        $election = Election::findOrFail($electionId);

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
        // Get active election - use isActive() method
        $election = Election::where('is_active', true)
            ->get()
            ->first(function ($e) {
                return $e->isActive();
            });

        // No active election
        if (!$election) {
            return Inertia::render('voter/viewCandidates', [
                'election' => null,
                'candidates' => [],
                'positions' => [],
                'message' => 'No active election at the moment. Please check back later.'
            ]);
        }

        // Get all candidates for this election with their position and user info
        $candidates = Candidate::whereHas('position', function($query) use ($election) {
                $query->where('election_id', $election->id);
            })
            ->with(['position', 'user'])
            ->get()
            ->map(function($candidate) {
                return [
                    'id' => $candidate->id,
                    'name' => $candidate->user->name,
                    'email' => $candidate->user->email,
                    'position' => $candidate->position->name,
                    'position_id' => $candidate->position_id,
                    'party' => $candidate->party_affiliation,
                    'platform' => $candidate->platform,
                    'image' => $candidate->photo ? '/storage/candidates/' . $candidate->photo : null,
                    'course' => $candidate->program . ' ' . $candidate->year . $candidate->section,
                    'quote' => $candidate->quote,
                ];
            });

        // Get unique positions for filter
        $positions = Position::where('election_id', $election->id)
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
