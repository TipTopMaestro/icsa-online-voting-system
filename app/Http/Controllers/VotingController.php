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
        
        // Get active election
        $election = Election::where('is_active', true)
            ->where('start_datetime', '<=', now())
            ->where('end_datetime', '>=', now())
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
     * Store votes (submit ballot)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Validate request
        $validated = $request->validate([
            'election_id' => 'required|exists:elections,id',
            'votes' => 'required|array|min:1', // At least 1 vote required
            'votes.*.position_id' => 'required|exists:positions,id',
            'votes.*.candidate_ids' => 'required|array|min:1',
            'votes.*.candidate_ids.*' => 'required|exists:candidates,id',
        ]);

        // Get election
        $election = Election::findOrFail($validated['election_id']);

        // Validate election is active
        if (!$election->isActive()) {
            return redirect()->back()->withErrors([
                'error' => 'This election is no longer active.'
            ]);
        }

        // Check if already voted
        $hasVoted = Vote::where('user_id', $user->id)
            ->where('election_id', $election->id)
            ->exists();

        if ($hasVoted) {
            return redirect()->back()->withErrors([
                'error' => 'You have already voted in this election.'
            ]);
        }

        // Validate each position's candidate selections
        foreach ($validated['votes'] as $vote) {
            $position = Position::findOrFail($vote['position_id']);
            
            // Check max_selection limit
            if (count($vote['candidate_ids']) > $position->max_selection) {
                return redirect()->back()->withErrors([
                    'error' => "Too many candidates selected for position: {$position->name}. Maximum allowed: {$position->max_selection}"
                ]);
            }

            // Validate candidates belong to this position
            foreach ($vote['candidate_ids'] as $candidateId) {
                $candidate = Candidate::findOrFail($candidateId);
                if ($candidate->position_id != $position->id) {
                    return redirect()->back()->withErrors([
                        'error' => 'Invalid candidate for position.'
                    ]);
                }
            }
        }

        // Use database transaction for atomicity
        DB::beginTransaction();
        
        try {
            // Store all votes
            foreach ($validated['votes'] as $vote) {
                foreach ($vote['candidate_ids'] as $candidateId) {
                    Vote::create([
                        'user_id' => $user->id,
                        'election_id' => $election->id,
                        'position_id' => $vote['position_id'],
                        'candidate_id' => $candidateId,
                    ]);

                    // Increment candidate vote count
                    $candidate = Candidate::find($candidateId);
                    $candidate->increment('votes_count');
                }
            }

            DB::commit();

            // Redirect to receipt page
            return redirect()->route('voter.receipt', [
                'election_id' => $election->id
            ])->with('success', 'Your vote has been submitted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Vote submission failed', [
                'user_id' => $user->id,
                'election_id' => $election->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->withErrors([
                'error' => 'Failed to submit votes. Please try again.'
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
        // Get active election
        $election = Election::where('is_active', true)
            ->where('start_datetime', '<=', now())
            ->where('end_datetime', '>=', now())
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
