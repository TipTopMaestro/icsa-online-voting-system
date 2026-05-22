<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CandidateController extends Controller
{
    public function dashboard()
    {
        try {
            $user = Auth::user();
            
            // Fetch candidate's comprehensive data from optimized view
            $candidateData = DB::table('view_candidate_dashboard')
                ->where('user_id', $user->id)
                ->first();
            
            if (!$candidateData) {
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
            
            $recentAnnouncements = DB::table('announcements')
                ->where('is_published', 1)
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
            
            return Inertia::render('candidate/Dashboard', [
                'user' => [
                    'name' => $user->name,
                    'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                ],
                'activeElection' => [
                    'id' => $candidateData->election_id,
                    'name' => $candidateData->election_title,
                    'description' => $candidateData->election_description,
                    'start_datetime' => $candidateData->start_datetime,
                    'end_datetime' => $candidateData->end_datetime,
                    'is_ongoing' => $candidateData->election_status === 'active',
                ],
                'candidatePosition' => [
                    'id' => $candidateData->position_id,
                    'name' => $candidateData->position_name,
                ],
                'recentAnnouncements' => $recentAnnouncements,
                'statistics' => [
                    'votesReceived' => (int)($candidateData->votes_count ?? 0),
                    'totalVoters' => (int)($candidateData->total_system_voters ?? 0),
                    'votePercentage' => (float)($candidateData->vote_percentage ?? 0),
                    'ranking' => (int)($candidateData->ranking ?? 0),
                    'totalCandidates' => (int)($candidateData->total_candidates_in_position ?? 0),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Candidate Dashboard Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }
    
    public function profile()
    {
        try {
            $user = Auth::user();
            
            // Get candidate info using optimized dashboard view
            $candidate = DB::table('view_candidate_dashboard')
                ->where('user_id', $user->id)
                ->first();
            
            return Inertia::render('candidate/Profile', [
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'photo' => $candidate && $candidate->candidate_photo 
                        ? asset('storage/candidates/' . $candidate->candidate_photo)
                        : null,
                ],
                'candidate' => $candidate ? [
                    'position' => $candidate->position_name,
                    'partylist' => $candidate->partylist ?? 'Independent',
                    'platform' => $candidate->platform ?? '',
                    'course' => $candidate->course ?? '',
                    'year_level' => $candidate->year_level ?? '',
                    'section' => $candidate->section ?? '',
                ] : null,
            ]);
        } catch (\Exception $e) {
            \Log::error('Candidate Profile Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }
    
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        $user = Auth::user();
        $candidate = DB::table('candidates')->where('user_id', $user->id)->first();
        
        if (!$candidate) {
            return back()->withErrors(['photo' => 'Candidate record not found']);
        }
        
        // Delete old photo if exists
        if ($candidate->photo) {
            Storage::disk('public')->delete('candidates/' . $candidate->photo);
        }
        
        // Store new photo in candidates folder
        $file = $request->file('photo');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('candidates', $filename, 'public');
        
        // Use stored procedure sp_UpdateCandidatePhoto
        DB::statement('CALL sp_UpdateCandidatePhoto(?, ?)', [$user->id, $filename]);
        
        return back()->with('success', 'Photo updated successfully');
    }
    
    public function updatePlatform(Request $request)
    {
        $request->validate([
            'platform' => 'required|string|max:1000',
        ]);
        
        // Use stored procedure sp_UpdateCandidatePlatform
        DB::statement('CALL sp_UpdateCandidatePlatform(?, ?)', [Auth::id(), $request->platform]);
        
        return back()->with('success', 'Platform updated successfully');
    }
    
    public function announcements()
    {
        $announcements = DB::table('announcements')
            ->join('users', 'announcements.created_by', '=', 'users.id')
            ->select('announcements.*', 'users.name as creator_name')
            ->where('is_published', true)
            ->whereIn('audience', ['all', 'voters', 'candidates'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return Inertia::render('candidate/Announcements', [
            'announcements' => $announcements,
        ]);
    }
    
    public function results(Request $request)
    {
        try {
            $user = Auth::user();
            
            $candidate = DB::table('candidates')->where('user_id', $user->id)->first();
            
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
            
            // Fallback to candidate's own election if the requested ID is not found
            $election = $allElections->firstWhere('id', $electionId) ?? $allElections->firstWhere('id', $candidate->election_id);

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

            // If no election can be found at all, return empty state instead of crashing
            if (!$election) {
                return Inertia::render('candidate/Results', [
                    'elections' => $electionOptions,
                    'selectedElection' => null,
                    'positions' => [],
                    'results' => [],
                    'statistics' => null,
                ]);
            }
            
            // Fetch pre-calculated results from our database view (now includes photo and details)
            $viewResults = DB::table('view_election_results')
                ->where('election_id', $election->id)
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
                        'votes' => (int) ($row->votes_count ?? 0),
                        'percentage' => (float) ($row->vote_percentage ?? 0),
                        'isWinner' => ($row->current_rank ?? 0) == 1 && ($row->votes_count ?? 0) > 0,
                        'partylist' => $row->partylist ?? 'N/A',
                        'course' => $row->course ?? 'N/A',
                        'year_level' => $row->year_level ?? 'N/A',
                        'section' => $row->section ?? 'N/A',
                    ];
                })
                ->values()
                ->toArray();
            }

            // Extract positions for the frontend mapping from the grouped keys
            $positions = collect(array_keys($results))->map(function ($name, $index) {
                return (object) [
                    'id' => $index + 1,
                    'name' => $name
                ];
            });
            
            // Calculate statistics using the view
            $electionStats = $allElections->firstWhere('id', $election->id);
            $totalRegisteredVoters = DB::table('users')->where('role', 'voter')->count();
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
                    'startDate' => Carbon::parse($election->start_datetime)->format('d M Y') . ' - ' . Carbon::parse($election->end_datetime)->format('d M Y'),
                    'is_active' => (bool)$election->is_active,
                ],
                'positions' => $positions,
                'results' => $results,
                'statistics' => $statistics,
            ]);
        } catch (\Exception $e) {
            \Log::error('Candidate Results Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }
    
    public function settings()
    {
        $user = Auth::user();
        $candidate = DB::table('candidates')->where('user_id', $user->id)->first();
        
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
        
        // Use stored procedure sp_UpdateCandidateProfile
        DB::statement('CALL sp_UpdateCandidateProfile(?, ?, ?)', [
            Auth::id(),
            $request->name,
            $request->email
        ]);
        
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
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }
        
        // Use stored procedure sp_UpdateUserPassword
        DB::statement('CALL sp_UpdateUserPassword(?, ?)', [
            Auth::id(),
            Hash::make($request->new_password)
        ]);
        
        return back()->with('success', 'Password updated successfully');
    }
}
