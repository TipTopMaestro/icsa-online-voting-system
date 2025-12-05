<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Election;
use App\Models\Announcement;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function voterDashboard() {
        $user = Auth::user();
        $activeElection = Election::where('is_active', 1)->first();
        
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
