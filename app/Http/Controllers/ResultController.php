<?php

namespace App\Http\Controllers;

use App\Models\Election;
use App\Models\Position;
use App\Models\Candidate;
use App\Models\Vote;
use App\Models\VoterProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ResultController extends Controller
{
    /**
     * Admin Results Page - Show results for all elections
     */
    public function result(Request $request)
    {
        // Get election ID from request, default to latest active or most recent
        $electionId = $request->input('election_id');
        
        if ($electionId) {
            $election = Election::findOrFail($electionId);
        } else {
            // Get active election (using isActive()) or latest election
            $election = Election::where('is_active', true)
                ->get()
                ->first(function ($e) {
                    return $e->isActive();
                }) ?? Election::latest()->first();
        }

        // Get all elections for selector dropdown
        $elections = Election::select('id', 'title', 'description', 'start_datetime', 'end_datetime', 'is_active')
            ->orderBy('start_datetime', 'desc')
            ->get()
            ->map(function ($e) {
                return [
                    'id' => $e->id,
                    'title' => $e->title,
                    'description' => $e->description,
                    'status' => $e->isActive() ? 'active' : 'closed',
                    'startDate' => $e->start_datetime->format('d M Y') . ' - ' . $e->end_datetime->format('d M Y'),
                    'is_active' => $e->isActive(),
                ];
            });

        if (!$election) {
            return Inertia::render('admin/result', [
                'elections' => $elections,
                'selectedElection' => null,
                'positions' => [],
                'results' => [],
                'statistics' => null,
            ]);
        }

        // Get results for the selected election
        $resultsData = $this->getElectionResults($election->id);

        return Inertia::render('admin/result', [
            'elections' => $elections,
            'selectedElection' => [
                'id' => $election->id,
                'title' => $election->title,
                'description' => $election->description,
                'status' => $election->isActive() ? 'active' : 'closed',
                'startDate' => $election->start_datetime->format('d M Y') . ' - ' . $election->end_datetime->format('d M Y'),
                'start_date' => $election->start_datetime->format('Y-m-d'),
                'end_date' => $election->end_datetime->format('Y-m-d'),
                'is_active' => $election->isActive(),
            ],
            'positions' => $resultsData['positions'],
            'results' => $resultsData['results'],
            'statistics' => $resultsData['statistics'],
        ]);
    }

    /**
     * Voter Results Page - Show results for active/ended elections
     */
    public function voterResult(Request $request)
    {
        // Get election ID from request, default to active election
        $electionId = $request->input('election_id');
        
        if ($electionId) {
            $election = Election::findOrFail($electionId);
        } else {
            // Get active election (using isActive()) or latest ended election
            $election = Election::where('is_active', true)
                ->get()
                ->first(function ($e) {
                    return $e->isActive();
                }) ?? Election::where('end_datetime', '<', now())->latest('end_datetime')->first();
        }

        // Get viewable elections for voter (active or ended)
        $elections = Election::where(function($query) {
                $query->where('is_active', true)
                    ->orWhere('end_datetime', '<', now());
            })
            ->select('id', 'title', 'description', 'start_datetime', 'end_datetime', 'is_active')
            ->orderBy('start_datetime', 'desc')
            ->get()
            ->map(function ($e) {
                return [
                    'id' => $e->id,
                    'title' => $e->title,
                    'description' => $e->description,
                    'status' => $e->isActive() ? 'active' : 'closed',
                    'startDate' => $e->start_datetime->format('d M Y') . ' - ' . $e->end_datetime->format('d M Y'),
                    'is_active' => $e->isActive(),
                ];
            });

        if (!$election) {
            return Inertia::render('voter/result', [
                'elections' => $elections,
                'selectedElection' => null,
                'positions' => [],
                'results' => [],
                'statistics' => null,
            ]);
        }

        // Get results for the selected election
        $resultsData = $this->getElectionResults($election->id);

        return Inertia::render('voter/result', [
            'elections' => $elections,
            'selectedElection' => [
                'id' => $election->id,
                'title' => $election->title,
                'description' => $election->description,
                'status' => $election->isActive() ? 'active' : 'closed',
                'startDate' => $election->start_datetime->format('d M Y') . ' - ' . $election->end_datetime->format('d M Y'),
                'start_date' => $election->start_datetime->format('Y-m-d'),
                'end_date' => $election->end_datetime->format('Y-m-d'),
                'is_active' => $election->isActive(),
            ],
            'positions' => $resultsData['positions'],
            'results' => $resultsData['results'],
            'statistics' => $resultsData['statistics'],
        ]);
    }

    /**
     * Get election results data using the view_election_results database view
     * Returns positions, results grouped by position, and statistics
     */
    private function getElectionResults($electionId)
    {
        $election = Election::findOrFail($electionId);

        // Get total registered voters (for turnout calculation)
        $totalRegisteredVoters = VoterProfile::count();

        // Get total voters who actually cast at least one vote in this election
        $totalVotersWhoVoted = Vote::where('election_id', $electionId)
            ->distinct('user_id')
            ->count('user_id');

        // Calculate turnout percentage
        $turnoutPercentage = $totalRegisteredVoters > 0 
            ? round(($totalVotersWhoVoted / $totalRegisteredVoters) * 100, 2) 
            : 0;

        // Fetch pre-calculated results from our database view
        $viewResults = DB::table('view_election_results')
            ->where('election_id', $electionId)
            ->get();

        // Get positions for this election
        $positions = Position::where('election_id', $electionId)
            ->select('id', 'name')
            ->orderBy('id')
            ->get();

        // Group the view results by position name for the frontend
        $results = [];
        foreach ($positions as $position) {
            $results[$position->name] = $viewResults->where('position_id', $position->id)
                ->map(function ($row) {
                    // Fetch the photo from the candidate model if needed or logic here
                    // Assuming candidate table has the photo path
                    $candidate = Candidate::find($row->candidate_id);
                    
                    return [
                        'id' => $row->candidate_id,
                        'name' => $row->candidate_name,
                        'photo' => $candidate && $candidate->photo 
                            ? asset('storage/candidates/' . $candidate->photo)
                            : asset('images/profile.png'),
                        'votes' => (int) $row->votes_count,
                        'percentage' => (float) $row->vote_percentage,
                        'isWinner' => $row->current_rank == 1 && $row->votes_count > 0,
                    ];
                })
                ->values()
                ->toArray();
        }

        // Statistics
        $statistics = [
            'totalVoters' => $totalRegisteredVoters,
            'votedCount' => $totalVotersWhoVoted,
            'abstainedCount' => $totalRegisteredVoters - $totalVotersWhoVoted,
            'turnoutPercentage' => $turnoutPercentage,
            'totalPositions' => $positions->count(),
            'totalCandidates' => Candidate::where('election_id', $electionId)->count(),
        ];

        return [
            'positions' => $positions->toArray(),
            'results' => $results,
            'statistics' => $statistics,
        ];
    }
}
