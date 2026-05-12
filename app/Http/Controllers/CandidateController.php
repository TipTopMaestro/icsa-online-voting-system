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
use Illuminate\Support\Facades\DB;

class CandidateController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        $candidate = Candidate::where('user_id', $user->id)
            ->with(['position'])
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
        
        // Fetch candidate's election statistics from optimized view
        $electionStats = DB::table('view_election_statistics')
            ->where('id', $candidate->election_id)
            ->first();
        
        if (!$electionStats) {
             return redirect()->route('dashboard')->withErrors(['error' => 'Election data not found.']);
        }

        // Get candidate's specific results from the results view
        $candidateResult = DB::table('view_election_results')
            ->where('candidate_id', $candidate->id)
            ->first();
        
        $totalVoters = User::where('role', 'voter')->count();
        $votesReceived = (int)($candidateResult->votes_count ?? 0);
        
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
                'id' => $electionStats->id,
                'name' => $electionStats->title,
                'description' => $electionStats->description,
                'start_datetime' => $electionStats->start_datetime,
                'end_datetime' => $electionStats->end_datetime,
                'is_ongoing' => $electionStats->status === 'active',
            ],
            'candidatePosition' => [
                'id' => $candidate->position->id,
                'name' => $candidate->position->name,
            ],
            'recentAnnouncements' => $recentAnnouncements,
            'statistics' => [
                'votesReceived' => $votesReceived,
                'totalVoters' => $totalVoters,
                'votePercentage' => $totalVoters > 0 ? round(($votesReceived / $totalVoters) * 100, 1) : 0,
                'ranking' => (int)($candidateResult->current_rank ?? 0),
                'totalCandidates' => (int)($electionStats->candidates_count ?? 0),
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
        
        // Get election metadata from optimized view
        $allElections = DB::table('view_election_statistics')
            ->orderBy('start_datetime', 'desc')
            ->get();
        
        $election = $allElections->firstWhere('id', $electionId) ?? $allElections->firstWhere('id', $candidate->election_id);

        // Map elections for selector dropdown
        $electionOptions = $allElections->map(function ($e) {
            return [
                'id' => $e->id,
                'title' => $e->title,
                'description' => $e->description,
                'status' => $e->status,
                'startDate' => \Carbon\Carbon::parse($e->start_datetime)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($e->end_datetime)->format('d M Y'),
                'is_active' => (bool)$e->is_active,
            ];
        });
        
        // Get all positions for this election
        $positions = Position::where('election_id', $election->id)
            ->select('id', 'name')
            ->orderBy('id')
            ->get();
        
        // Fetch pre-calculated results from our database view
        $viewResults = DB::table('view_election_results')
            ->where('election_id', $election->id)
            ->get();

        // Get results grouped by position
        $results = [];
        
        foreach ($positions as $position) {
            $results[$position->name] = $viewResults->where('position_id', $position->id)
                ->map(function ($row) {
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
                        'partylist' => $candidate->partylist ?? 'N/A',
                        'course' => $candidate->course ?? 'N/A',
                        'year_level' => $candidate->year_level ?? 'N/A',
                        'section' => $candidate->section ?? 'N/A',
                    ];
                })
                ->values()
                ->toArray();
        }
        
        // Calculate statistics using the view
        $electionStats = $allElections->firstWhere('id', $election->id);
        $totalRegisteredVoters = User::where('role', 'voter')->count();
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
        
        return Inertia::render('candidate/Results', [
            'elections' => $electionOptions,
            'selectedElection' => [
                'id' => $election->id,
                'title' => $election->title,
                'description' => $election->description,
                'status' => $election->status,
                'startDate' => \Carbon\Carbon::parse($election->start_datetime)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($election->end_datetime)->format('d M Y'),
                'is_active' => (bool)$election->is_active,
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
