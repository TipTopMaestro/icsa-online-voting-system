<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class ResultController extends Controller
{
    /**
     * Admin Results Page - Show results for all elections
     */
    public function result(Request $request)
    {
        // Get election metadata from optimized view
        $allElections = DB::table('view_election_statistics')
            ->orderBy('start_datetime', 'desc')
            ->get();

        // Get election ID from request, default to active or newest
        $electionId = $request->input('election_id');
        $election = null;

        if ($electionId) {
            $election = $allElections->firstWhere('id', $electionId);
        } else {
            $election = $allElections->firstWhere('is_active', 1) ?? $allElections->first();
        }

        // Map elections for selector dropdown
        $electionOptions = $allElections->map(function ($e) {
            return [
                'id' => $e->id,
                'title' => $e->title,
                'description' => $e->description,
                'status' => $e->status, // Use pre-calculated status from view
                'startDate' => Carbon::parse($e->start_datetime)->format('d M Y') . ' - ' . Carbon::parse($e->end_datetime)->format('d M Y'),
                'is_active' => (bool)$e->is_active,
            ];
        });

        if (!$election) {
            return Inertia::render('admin/result', [
                'elections' => $electionOptions,
                'selectedElection' => null,
                'positions' => [],
                'results' => [],
                'statistics' => null,
            ]);
        }

        // Get results for the selected election
        $resultsData = $this->getElectionResults($election->id);

        return Inertia::render('admin/result', [
            'elections' => $electionOptions,
            'selectedElection' => [
                'id' => $election->id,
                'title' => $election->title,
                'description' => $election->description,
                'status' => $election->status,
                'startDate' => Carbon::parse($election->start_datetime)->format('d M Y') . ' - ' . Carbon::parse($election->end_datetime)->format('d M Y'),
                'start_date' => Carbon::parse($election->start_datetime)->format('Y-m-d'),
                'end_date' => Carbon::parse($election->end_datetime)->format('Y-m-d'),
                'is_active' => (bool)$election->is_active,
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
        // Get viewable elections for voter from optimized view (active or ended)
        $allElections = DB::table('view_election_statistics')
            ->where('is_active', 1)
            ->orWhere('status', 'ended')
            ->orderBy('start_datetime', 'desc')
            ->get();

        // Get election ID from request, default to active
        $electionId = $request->input('election_id');
        $election = null;

        if ($electionId) {
            $election = $allElections->firstWhere('id', $electionId);
        } else {
            $election = $allElections->firstWhere('is_active', 1) ?? $allElections->first();
        }

        // Map elections for selector dropdown
        $electionOptions = $allElections->map(function ($e) {
            return [
                'id' => $e->id,
                'title' => $e->title,
                'description' => $e->description,
                'status' => $e->status,
                'startDate' => Carbon::parse($e->start_datetime)->format('d M Y') . ' - ' . Carbon::parse($e->end_datetime)->format('d M Y'),
                'is_active' => (bool)$e->is_active,
            ];
        });

        if (!$election) {
            return Inertia::render('voter/result', [
                'elections' => $electionOptions,
                'selectedElection' => null,
                'positions' => [],
                'results' => [],
                'statistics' => null,
            ]);
        }

        // Get results for the selected election
        $resultsData = $this->getElectionResults($election->id);

        return Inertia::render('voter/result', [
            'elections' => $electionOptions,
            'selectedElection' => [
                'id' => $election->id,
                'title' => $election->title,
                'description' => $election->description,
                'status' => $election->status,
                'startDate' => Carbon::parse($election->start_datetime)->format('d M Y') . ' - ' . Carbon::parse($election->end_datetime)->format('d M Y'),
                'start_date' => Carbon::parse($election->start_datetime)->format('Y-m-d'),
                'end_date' => Carbon::parse($election->end_datetime)->format('Y-m-d'),
                'is_active' => (bool)$election->is_active,
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
        // Get total registered voters (for turnout calculation)
        $totalRegisteredVoters = DB::table('voter_profiles')->count();

        // Get pre-calculated statistics from the view_election_statistics view
        $electionStats = DB::table('view_election_statistics')
            ->where('id', $electionId)
            ->first();

        // Fetch pre-calculated results from our database view (now includes photo and details)
        $viewResults = DB::table('view_election_results')
            ->where('election_id', $electionId)
            ->get();

        // Get results grouped by position name from the database view
        $results = [];
        $groupedResults = $viewResults->groupBy('position_name');
        
        foreach ($groupedResults as $positionName => $candidates) {
            $results[$positionName] = $candidates->map(function ($row) {
                return [
                    'id' => $row->candidate_id,
                    'name' => $row->candidate_name,
                    'photo' => $row->candidate_photo 
                        ? asset('storage/candidates/' . $row->candidate_photo)
                        : asset('images/profile.png'),
                    'votes' => (int) $row->votes_count,
                    'percentage' => (float) $row->vote_percentage,
                    'isWinner' => $row->current_rank == 1 && $row->votes_count > 0,
                    'partylist' => $row->partylist ?? 'Independent',
                ];
            })
            ->values()
            ->toArray();
        }

        // Extract positions for the frontend mapping from the grouped keys
        $positions = DB::table('positions')->where('election_id', $electionId)
            ->select('id', 'name')
            ->orderBy('id')
            ->get();

        // Statistics using values from our view
        $votedCount = (int)($electionStats->voted_count ?? 0);
        $statistics = [
            'totalVoters' => $totalRegisteredVoters,
            'votedCount' => $votedCount,
            'abstainedCount' => max(0, $totalRegisteredVoters - $votedCount),
            'turnoutPercentage' => $totalRegisteredVoters > 0 
                ? round(($votedCount / $totalRegisteredVoters) * 100, 2) 
                : 0,
            'totalPositions' => $positions->count(),
            'totalCandidates' => (int)($electionStats->candidates_count ?? 0),
        ];

        return [
            'positions' => $positions->toArray(),
            'results' => $results,
            'statistics' => $statistics,
        ];
    }
}
