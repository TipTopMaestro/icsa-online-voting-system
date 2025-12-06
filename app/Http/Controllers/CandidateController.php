<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Election;
use App\Models\Announcement;
use App\Models\Candidate;
use App\Models\User;
use Carbon\Carbon;

class CandidateController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        $candidate = Candidate::where('user_id', $user->id)
            ->with(['position', 'election'])
            ->first();
        
        if (!$candidate) {
            return Inertia::render('candidate/Dashboard', [
                'user' => [
                    'name' => $user->name,
                    'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                ],
                'activeElection' => null,
                'candidatePosition' => null,
                'recentAnnouncements' => [],
                'statistics' => [
                    'votesReceived' => 0,
                    'totalVoters' => 0,
                    'votePercentage' => 0,
                    'ranking' => 0,
                    'totalCandidates' => 0,
                ],
            ]);
        }
        
        $activeElection = $candidate->election;
        
        // Refresh election data to get latest is_active status from database
        $activeElection->refresh();
        
        // Use the election's isActive() method which checks both is_active field and date range
        $isElectionOngoing = $activeElection->isActive();
        
        $votesReceived = \DB::table('votes')
            ->where('candidate_id', $candidate->id)
            ->where('election_id', $activeElection->id)
            ->count();
        
        $totalVoters = User::where('role', 'voter')->count();
        
        $votePercentage = $totalVoters > 0 ? round(($votesReceived / $totalVoters) * 100, 1) : 0;
        
        $candidatesInPosition = Candidate::where('position_id', $candidate->position_id)
            ->where('election_id', $activeElection->id)
            ->withCount('votes')
            ->orderBy('votes_count', 'desc')
            ->get();
        
        $ranking = 0;
        $totalCandidates = $candidatesInPosition->count();
        
        foreach ($candidatesInPosition as $index => $c) {
            if ($c->id === $candidate->id) {
                $ranking = $index + 1;
                break;
            }
        }
        
        $recentAnnouncements = Announcement::where('is_published', 1)
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
        
        return Inertia::render('candidate/Dashboard', [
            'user' => [
                'name' => $user->name,
                'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
            ],
            'activeElection' => [
                'id' => $activeElection->id,
                'name' => $activeElection->title,
                'description' => $activeElection->description,
                'start_datetime' => $activeElection->start_datetime,
                'end_datetime' => $activeElection->end_datetime,
                'is_ongoing' => $isElectionOngoing,
            ],
            'candidatePosition' => [
                'id' => $candidate->position->id,
                'name' => $candidate->position->name,
            ],
            'recentAnnouncements' => $recentAnnouncements,
            'statistics' => [
                'votesReceived' => $votesReceived,
                'totalVoters' => $totalVoters,
                'votePercentage' => $votePercentage,
                'ranking' => $ranking,
                'totalCandidates' => $totalCandidates,
            ],
        ]);
    }
    
    public function profile()
    {
        $user = Auth::user();
        
        // Get candidate info with relations
        $candidate = Candidate::where('user_id', $user->id)
            ->with(['position', 'election'])
            ->first();
        
        return Inertia::render('candidate/Profile', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'photo' => $candidate && $candidate->photo 
                    ? asset('storage/candidates/' . $candidate->photo)
                    : null,
            ],
            'candidate' => $candidate ? [
                'position' => $candidate->position->name,
                'partylist' => $candidate->partylist ?? 'Independent',
                'platform' => $candidate->platform ?? '',
                'course' => $candidate->course ?? '',
                'year_level' => $candidate->year_level ?? '',
                'section' => $candidate->section ?? '',
            ] : null,
        ]);
    }
    
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        $user = Auth::user();
        $candidate = Candidate::where('user_id', $user->id)->first();
        
        if (!$candidate) {
            return back()->withErrors(['photo' => 'Candidate record not found']);
        }
        
        // Delete old photo if exists
        if ($candidate->photo) {
            \Storage::disk('public')->delete('candidates/' . $candidate->photo);
        }
        
        // Store new photo in candidates folder
        $file = $request->file('photo');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('candidates', $filename, 'public');
        
        $candidate->photo = $filename;
        $candidate->save();
        
        return back()->with('success', 'Photo updated successfully');
    }
    
    public function updatePlatform(Request $request)
    {
        $request->validate([
            'platform' => 'required|string|max:1000',
        ]);
        
        $user = Auth::user();
        $candidate = Candidate::where('user_id', $user->id)->first();
        
        if ($candidate) {
            $candidate->platform = $request->platform;
            $candidate->save();
        }
        
        return back()->with('success', 'Platform updated successfully');
    }
    
    public function announcements()
    {
        $announcements = Announcement::with('creator')
            ->where('is_published', true)
            ->whereIn('audience', ['all', 'voters', 'candidates'])
            ->latest()
            ->get();
        
        return Inertia::render('candidate/Announcements', [
            'announcements' => $announcements,
        ]);
    }
    
    public function results(Request $request)
    {
        $user = Auth::user();
        
        $candidate = Candidate::where('user_id', $user->id)
            ->with('election')
            ->first();
        
        if (!$candidate) {
            return Inertia::render('candidate/Results', [
                'elections' => [],
                'selectedElection' => null,
                'positions' => [],
                'results' => [],
                'statistics' => null,
            ]);
        }
        
        $electionId = $request->input('election_id', $candidate->election_id);
        $election = Election::find($electionId) ?? $candidate->election;
        
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
        
        // Get all positions for this election
        $positions = \DB::table('positions')
            ->join('candidates', 'positions.id', '=', 'candidates.position_id')
            ->where('candidates.election_id', $election->id)
            ->select('positions.id', 'positions.name')
            ->distinct()
            ->get();
        
        // Get results grouped by position
        $results = [];
        
        foreach ($positions as $position) {
            $candidates = Candidate::where('position_id', $position->id)
                ->where('election_id', $election->id)
                ->with('user')
                ->get()
                ->map(function ($candidate) use ($election) {
                    $voteCount = \DB::table('votes')
                        ->where('candidate_id', $candidate->id)
                        ->where('election_id', $election->id)
                        ->count();
                    
                    return [
                        'id' => $candidate->id,
                        'name' => $candidate->user->name,
                        'photo' => $candidate->photo 
                            ? asset('storage/candidates/' . $candidate->photo)
                            : asset('images/profile.png'),
                        'votes' => $voteCount,
                        'partylist' => $candidate->partylist,
                        'course' => $candidate->course,
                        'year_level' => $candidate->year_level,
                        'section' => $candidate->section,
                    ];
                })
                ->sortByDesc('votes')
                ->values();
            
            // Calculate total votes and percentages
            $totalVotesForPosition = $candidates->sum('votes');
            
            $candidates = $candidates->map(function ($candidate, $index) use ($totalVotesForPosition) {
                $percentage = $totalVotesForPosition > 0 
                    ? round(($candidate['votes'] / $totalVotesForPosition) * 100, 2) 
                    : 0;
                
                return array_merge($candidate, [
                    'percentage' => $percentage,
                    'isWinner' => $index === 0,
                ]);
            });
            
            $results[$position->name] = $candidates->toArray();
        }
        
        // Calculate statistics
        $totalVoters = \DB::table('votes')
            ->where('election_id', $election->id)
            ->distinct('user_id')
            ->count('user_id');
        
        $totalRegisteredVoters = User::where('role', 'voter')->count();
        $turnoutPercentage = $totalRegisteredVoters > 0 
            ? round(($totalVoters / $totalRegisteredVoters) * 100, 2) 
            : 0;
        
        $statistics = [
            'totalVoters' => $totalRegisteredVoters,
            'votedCount' => $totalVoters,
            'abstainedCount' => $totalRegisteredVoters - $totalVoters,
            'turnoutPercentage' => $turnoutPercentage,
            'totalPositions' => count($positions),
            'totalCandidates' => Candidate::where('election_id', $election->id)->count(),
        ];
        
        return Inertia::render('candidate/Results', [
            'elections' => $elections,
            'selectedElection' => [
                'id' => $election->id,
                'title' => $election->title,
                'description' => $election->description,
                'status' => $election->isActive() ? 'active' : 'closed',
                'startDate' => $election->start_datetime->format('d M Y') . ' - ' . $election->end_datetime->format('d M Y'),
                'is_active' => $election->isActive(),
            ],
            'positions' => $positions,
            'results' => $results,
            'statistics' => $statistics,
        ]);
    }
    
    public function settings()
    {
        $user = Auth::user();
        $candidate = Candidate::where('user_id', $user->id)->first();
        
        return Inertia::render('candidate/Settings', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'photo' => $candidate && $candidate->photo 
                    ? asset('storage/candidates/' . $candidate->photo)
                    : null,
            ],
        ]);
    }
    
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
        ]);
        
        $user = Auth::user();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        
        return back()->with('success', 'Profile updated successfully');
    }
    
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);
        
        $user = Auth::user();
        
        // Check current password
        if (!\Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }
        
        // Update password
        $user->password = \Hash::make($request->new_password);
        $user->save();
        
        return back()->with('success', 'Password updated successfully');
    }
}
