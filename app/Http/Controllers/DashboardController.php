<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Election;
use App\Models\Announcement;
use App\Models\User;
use App\Models\Candidate;
use App\Models\Position;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function adminDashboard() {
        // Get overall statistics
        $totalVoters = User::where('role', 'voter')->count();
        
        // Get active election - use isActive() method
        $activeElection = Election::where('is_active', 1)
            ->with(['positions.candidates'])
            ->get()
            ->first(function ($e) {
                return $e->isActive();
            });
        
        $activeElectionsCount = $activeElection ? 1 : 0;
        $totalCandidates = Candidate::count();
        
        // Calculate accurate turnout for active election
        $totalVotes = 0;
        if ($activeElection) {
            // Count unique voters who voted in the active election
            $totalVotes = \DB::table('votes')
                ->where('election_id', $activeElection->id)
                ->distinct('user_id')
                ->count('user_id');
        }
        
        $chartData = null;
        if ($activeElection) {
            // Get vote counts per candidate for active election
            $voteCounts = \DB::table('votes')
                ->join('candidates', 'votes.candidate_id', '=', 'candidates.id')
                ->join('users', 'candidates.user_id', '=', 'users.id')
                ->join('positions', 'candidates.position_id', '=', 'positions.id')
                ->where('votes.election_id', $activeElection->id)
                ->select(
                    'candidates.id',
                    'users.name as candidate_name',
                    'positions.name as position_name'
                )
                ->selectRaw('COUNT(votes.id) as vote_count')
                ->groupBy('candidates.id', 'users.name', 'positions.name')
                ->get();
            
            $chartData = [
                'labels' => $voteCounts->pluck('candidate_name')->toArray(),
                'data' => $voteCounts->pluck('vote_count')->toArray(),
                'positions' => $voteCounts->pluck('position_name')->toArray(),
            ];
        }
        
        // Get recent activities (last 5)
        $activities = [];
        
        // Recent votes
        $recentVotes = \DB::table('votes')
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
        $recentVoters = User::where('role', 'voter')
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();
        
        foreach ($recentVoters as $voter) {
            $activities[] = [
                'type' => 'voter',
                'title' => 'New voter registered',
                'description' => $voter->name . ($voter->course ? ' (' . $voter->course . ')' : ''),
                'time' => $voter->created_at->diffForHumans(),
                'timestamp' => $voter->created_at->timestamp,
                'icon' => 'userPlus',
                'color' => 'blue'
            ];
        }
        
        // Recent announcements
        $recentAnnouncement = Announcement::where('is_published', 1)
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($recentAnnouncement) {
            $activities[] = [
                'type' => 'announcement',
                'title' => 'New announcement published',
                'description' => $recentAnnouncement->title,
                'time' => $recentAnnouncement->created_at->diffForHumans(),
                'timestamp' => $recentAnnouncement->created_at->timestamp,
                'icon' => 'megaphone',
                'color' => 'purple'
            ];
        }
        
        // Sort activities by timestamp (most recent first)
        usort($activities, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        $activities = array_slice($activities, 0, 5);
        
        // Get all elections with stats
        $elections = Election::withCount(['votes', 'candidates', 'positions'])
            ->get()
            ->map(function ($election) use ($totalVoters) {
                $votedCount = \DB::table('votes')
                    ->where('election_id', $election->id)
                    ->distinct('user_id')
                    ->count('user_id');
                
                return [
                    'id' => $election->id,
                    'title' => $election->title,
                    'description' => $election->description,
                    'start_datetime' => $election->start_datetime,
                    'end_datetime' => $election->end_datetime,
                    'is_active' => $election->is_active,
                    'status' => $election->status,
                    'positions_count' => $election->positions_count,
                    'candidates_count' => $election->candidates_count,
                    'votes_count' => $election->votes_count,
                    'voted_count' => $votedCount,
                    'total_voters' => $totalVoters,
                    'turnout_percentage' => $totalVoters > 0 ? round(($votedCount / $totalVoters) * 100, 1) : 0,
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
            'activeElection' => $activeElection ? [
                'id' => $activeElection->id,
                'title' => $activeElection->title,
            ] : null,
        ]);
    }

    public function voterDashboard() {
        $user = Auth::user();
        
        // Get active election - use isActive() method
        $activeElection = Election::where('is_active', 1)
            ->get()
            ->first(function ($e) {
                return $e->isActive();
            });
        
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
            $hasVoted = \DB::table('votes')
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
                'name' => $activeElection->name,
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
        $dashboardData['recentAnnouncements'] = Announcement::where('is_published', 1)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($announcement) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'content' => $announcement->content,
                    'created_at' => $announcement->created_at->diffForHumans(),
                ];
            });
        
        // Get voter statistics
        $totalVotes = \DB::table('votes')
            ->where('user_id', $user->id)
            ->count();
        
        $participatedElections = \DB::table('votes')
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
