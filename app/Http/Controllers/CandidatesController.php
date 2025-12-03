<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\User;
use App\Models\Election;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CandidatesController extends Controller 
{
    public function index(Request $request)
    {
        $query = Candidate::with(['user', 'election', 'position'])
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('partylist', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by election
        if ($request->has('election_id') && $request->election_id) {
            $query->where('election_id', $request->election_id);
        }

        // Filter by position
        if ($request->has('position_id') && $request->position_id) {
            $query->where('position_id', $request->position_id);
        }

        // Filter by partylist
        if ($request->has('partylist') && $request->partylist) {
            $query->where('partylist', 'like', "%{$request->partylist}%");
        }

        // Filter by course
        if ($request->has('course') && $request->course) {
            $query->where('course', $request->course);
        }

        // Filter by year level
        if ($request->has('year_level') && $request->year_level) {
            $query->where('year_level', $request->year_level);
        }

        $candidates = $query->paginate(15);

        // Get elections and positions for filters
        $elections = Election::orderBy('title')->get(['id', 'title']);
        $positions = Position::orderBy('name')->get(['id', 'name', 'election_id']);

        return Inertia::render('admin/candidates', [
            'candidates' => $candidates,
            'elections' => $elections,
            'positions' => $positions,
            'filters' => $request->only(['search', 'election_id', 'position_id', 'partylist', 'course', 'year_level']),
        ]);
    }

    public function store(Request $request)
    {
        \Log::info('Candidate Store Request', [
            'all_data' => $request->all(),
            'has_file' => $request->hasFile('photo'),
            'files' => $request->allFiles(),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'election_id' => 'required|exists:elections,id',
            'position_id' => 'required|exists:positions,id',
            'partylist' => 'required|string|max:255',
            'platform' => 'required|string|min:50',
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'course' => ['required', Rule::in(['BSIT', 'BSIS'])],
            'year_level' => ['required', Rule::in(['1', '2', '3', '4'])],
            'section' => 'nullable|string|max:10',
        ]);

        \Log::info('Validation passed', $validated);

        // Check if user already running for a position in this election
        $existingCandidate = Candidate::whereHas('user', function ($q) use ($validated) {
                $q->where('email', $validated['email']);
            })
            ->where('election_id', $validated['election_id'])
            ->exists();

        if ($existingCandidate) {
            return response()->json([
                'success' => false,
                'message' => 'This person is already running for a position in this election.',
                'errors' => ['email' => 'This person is already running for a position in this election.']
            ], 422);
        }

        try {
            DB::beginTransaction();

            \Log::info('Starting candidate creation transaction');

            // Create user account
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make('password123'), // Default password
                'role' => 'candidate',
            ]);

            \Log::info('User created', ['user_id' => $user->id]);

            // Handle photo upload
            $photoName = time() . '_' . $request->file('photo')->getClientOriginalName();
            $request->file('photo')->storeAs('candidates', $photoName, 'public');

            \Log::info('Photo uploaded', ['photo' => $photoName]);

            // Create candidate
            $candidate = Candidate::create([
                'user_id' => $user->id,
                'election_id' => $validated['election_id'],
                'position_id' => $validated['position_id'],
                'partylist' => $validated['partylist'],
                'platform' => $validated['platform'],
                'photo' => $photoName,
                'course' => $validated['course'],
                'year_level' => $validated['year_level'],
                'section' => $validated['section'] ?? '',
                'votes_count' => 0,
            ]);

            \Log::info('Candidate record created', ['candidate_id' => $candidate->id]);

            DB::commit();

            \Log::info('Transaction committed successfully');

            \Log::info('Candidate created successfully', [
                'user_id' => $user->id,
                'candidate_id' => $candidate->id,
                'candidate_photo' => $photoName,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Candidate created successfully.',
                'candidate' => $candidate->load(['user', 'election', 'position']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Candidate creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Delete uploaded photo if exists
            if (isset($photoName)) {
                Storage::disk('public')->delete('candidates/' . $photoName);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create candidate: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Candidate $candidate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'election_id' => 'required|exists:elections,id',
            'position_id' => 'required|exists:positions,id',
            'partylist' => 'required|string|max:255',
            'platform' => 'required|string|min:50',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'course' => ['required', Rule::in(['BSIT', 'BSIS'])],
            'year_level' => ['required', Rule::in(['1', '2', '3', '4'])],
            'section' => 'nullable|string|max:10',
        ]);

        try {
            DB::beginTransaction();

            // Update user name
            $candidate->user->update([
                'name' => $validated['name'],
            ]);

            // Handle photo upload if new photo provided
            if ($request->hasFile('photo')) {
                // Delete old photo
                Storage::disk('public')->delete('candidates/' . $candidate->photo);

                // Upload new photo
                $photoName = time() . '_' . $request->file('photo')->getClientOriginalName();
                $request->file('photo')->storeAs('candidates', $photoName, 'public');
                $validated['photo'] = $photoName;
            }

            // Update candidate
            $candidate->update([
                'election_id' => $validated['election_id'],
                'position_id' => $validated['position_id'],
                'partylist' => $validated['partylist'],
                'platform' => $validated['platform'],
                'photo' => $validated['photo'] ?? $candidate->photo,
                'course' => $validated['course'],
                'year_level' => $validated['year_level'],
                'section' => $validated['section'] ?? '',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Candidate updated successfully.',
                'candidate' => $candidate->fresh()->load(['user', 'election', 'position']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded photo if exists
            if (isset($photoName)) {
                Storage::disk('public')->delete('candidates/' . $photoName);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update candidate: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Candidate $candidate)
    {
        try {
            DB::beginTransaction();

            // Delete photo
            Storage::disk('public')->delete('candidates/' . $candidate->photo);

            // Delete user account (will cascade delete candidate)
            $candidate->user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Candidate deleted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete candidate: ' . $e->getMessage(),
            ], 500);
        }
    }
}

