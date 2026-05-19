<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function adminDashboard() {
        // Fetch all election statistics from the optimized database view
        $allStats = DB::table('view_election_statistics')->orderBy('created_at', 'desc')->get();
        
        // Find the active election record from the view
        $activeElectionRecord = $allStats->firstWhere('is_active', 1);
        
        // Get overall statistics
        $totalVoters = DB::table('users')->where('role', 'voter')->count();
        $activeElectionsCount = $activeElectionRecord ? 1 : 0;
        $totalCandidates = DB::table('candidates')->count();
        
        // Calculate accurate turnout for active election from the view
        $totalVotes = $activeElectionRecord ? $activeElectionRecord->voted_count : 0;
        
        $chartData = null;
        if ($activeElectionRecord) {
            // Get vote counts per candidate for active election using our optimized results view
            $voteCounts = DB::table('view_election_results')
                ->where('election_id', $activeElectionRecord->id)
                ->get();
            
            $chartData = [
                'labels' => $voteCounts->pluck('candidate_name')->toArray(),
                'data' => $voteCounts->pluck('votes_count')->toArray(),
                'positions' => $voteCounts->pluck('position_name')->toArray(),
            ];
        }
        
        // Get recent activities (last 5)
        $activities = [];
        
        // Recent votes
        $recentVotes = DB::table('votes')
            ->join('elections', 'votes.election_id', '=', 'elections.id')
            ->select('elections.title as election_name', 'votes.created_at')
            ->orderBy('votes.created_at', 'desc')
            ->limit(2)
            ->get();
        
        foreach ($recentVotes as $vote) {
            $activities[] = [
                'type' => 'vote',
                'title' => 'New vote cast',
                'description' => $vote->election_name,
                'time' => Carbon::parse($vote->created_at)->diffForHumans(),
                'timestamp' => Carbon::parse($vote->created_at)->timestamp,
                'icon' => 'checkCircle',
                'color' => 'green'
            ];
        }
        
        // Recent voter registrations
        $recentVoters = DB::table('users')->where('role', 'voter')
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();
        
        foreach ($recentVoters as $voter) {
            $activities[] = [
                'type' => 'voter',
                'title' => 'New voter registered',
                'description' => $voter->name,
                'time' => Carbon::parse($voter->created_at)->diffForHumans(),
                'timestamp' => Carbon::parse($voter->created_at)->timestamp,
                'icon' => 'userPlus',
                'color' => 'blue'
            ];
        }
        
        // Recent announcements
        $recentAnnouncement = DB::table('announcements')->where('is_published', 1)
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($recentAnnouncement) {
            $activities[] = [
                'type' => 'announcement',
                'title' => 'New announcement published',
                'description' => $recentAnnouncement->title,
                'time' => Carbon::parse($recentAnnouncement->created_at)->diffForHumans(),
                'timestamp' => Carbon::parse($recentAnnouncement->created_at)->timestamp,
                'icon' => 'megaphone',
                'color' => 'purple'
            ];
        }
        
        // Sort activities by timestamp (most recent first)
        usort($activities, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        $activities = array_slice($activities, 0, 5);
        
        // Format elections list from the view data
        $elections = $allStats->map(function ($row) use ($totalVoters) {
            return [
                'id' => $row->id,
                'title' => $row->title,
                'description' => $row->description,
                'start_datetime' => $row->start_datetime,
                'end_datetime' => $row->end_datetime,
                'is_active' => $row->is_active,
                'status' => $row->status,
                'positions_count' => $row->positions_count,
                'candidates_count' => $row->candidates_count,
                'votes_count' => $row->votes_count,
                'voted_count' => $row->voted_count,
                'total_voters' => $totalVoters,
                'turnout_percentage' => $totalVoters > 0 ? round(($row->voted_count / $totalVoters) * 100, 1) : 0,
            ];
        });
        
        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'totalVoters' => $totalVoters,
                'activeElections' => $activeElectionsCount,
                'totalVotes' => $totalVotes,
                'totalCandidates' => $totalCandidates,
            ],
            'chartData' => $chartData,
            'activities' => $activities,
            'elections' => $elections,
            'activeElection' => $activeElectionRecord ? [
                'id' => $activeElectionRecord->id,
                'title' => $activeElectionRecord->title,
            ] : null,
        ]);
    }

    public function voterDashboard() {
        $user = Auth::user();
        
        // Fetch active election from the optimized view
        $activeElection = DB::table('view_election_statistics')
            ->where('is_active', 1)
            ->first();
        
        $dashboardData = [
            'user' => [
                'name' => $user->name,
                'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
            ],
            'activeElection' => null,
            'hasVoted' => false,
            'votingStatus' => 'No active election',
            'timeRemaining' => null,
            'recentAnnouncements' => [],
            'statistics' => [
                'totalElectionsParticipated' => 0,
                'totalVotesCast' => 0,
            ]
        ];
        
        if ($activeElection) {
            // Check if user has voted
            $hasVoted = DB::table('votes')
                ->where('user_id', $user->id)
                ->where('election_id', $activeElection->id)
                ->exists();
            
            // Calculate time remaining
            $now = Carbon::now();
            $endTime = Carbon::parse($activeElection->end_datetime);
            $timeRemaining = null;
            
            if ($endTime->isFuture()) {
                $diff = $now->diff($endTime);
                $timeRemaining = [
                    'days' => $diff->d,
                    'hours' => $diff->h,
                    'minutes' => $diff->i,
                    'seconds' => $diff->s,
                    'total_seconds' => $now->diffInSeconds($endTime)
                ];
            }
            
            $dashboardData['activeElection'] = [
                'id' => $activeElection->id,
                'name' => $activeElection->title, // Mapped title to name for frontend compatibility
                'description' => $activeElection->description,
                'start_datetime' => $activeElection->start_datetime,
                'end_datetime' => $activeElection->end_datetime,
                'is_ongoing' => $endTime->isFuture(),
            ];
            
            $dashboardData['hasVoted'] = $hasVoted;
            $dashboardData['votingStatus'] = $hasVoted ? 'You have voted' : 'Not yet voted';
            $dashboardData['timeRemaining'] = $timeRemaining;
        }
        
        // Get recent announcements
        $dashboardData['recentAnnouncements'] = DB::table('announcements')->where('is_published', 1)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($announcement) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'content' => $announcement->content,
                    'created_at' => Carbon::parse($announcement->created_at)->diffForHumans(),
                ];
            });
        
        // Get voter statistics
        $totalVotes = DB::table('votes')
            ->where('user_id', $user->id)
            ->count();
        
        $participatedElections = DB::table('votes')
            ->where('user_id', $user->id)
            ->distinct('election_id')
            ->count('election_id');
        
        $dashboardData['statistics'] = [
            'totalElectionsParticipated' => $participatedElections,
            'totalVotesCast' => $totalVotes,
        ];
        
        return Inertia::render('voter/Dashboard', $dashboardData);
    }
}
