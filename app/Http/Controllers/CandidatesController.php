<?php

namespace App\Http\Controllers;

use App\Mail\CandidateCredentialsMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CandidatesController extends Controller 
{
    /**
     * Generate a unique password for a candidate
     * Format: firstname + 4 random alphanumeric characters
     */
    private function generateUniquePassword(string $name): string
    {
        $firstName = strtolower(explode(' ', trim($name))[0]);
        $firstName = preg_replace('/[^a-z0-9]/', '', $firstName);
        
        do {
            $randomChars = Str::upper(Str::random(4));
            $password = $firstName . $randomChars;
            $hashedPassword = Hash::make($password);
            // Since we can't reliably check unique by hashed password, we just ensure no collision in logic if possible, 
            // but usually we check by email. Here we just return the generated password.
            $exists = DB::table('users')->where('email', 'like', $firstName . '%')->where('password', $hashedPassword)->exists();
        } while ($exists);
        
        return $password;
    }

    public function index(Request $request)
    {
        $query = DB::table('view_candidates_details')
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('partylist', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%");
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

        $candidates = $query->paginate(15)->through(function ($row) {
            // Ensure we have a user object even if name/email are null
            return [
                'id' => $row->id,
                'user_id' => $row->user_id,
                'election_id' => $row->election_id,
                'position_id' => $row->position_id,
                'partylist' => $row->partylist ?? 'Independent',
                'platform' => $row->platform ?? '',
                'photo' => $row->photo ?? '',
                'course' => $row->course ?? '',
                'year_level' => $row->year_level ?? '',
                'section' => $row->section ?? '',
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'votes_count' => (int)($row->votes_count ?? 0),
                'user' => [
                    'id' => (int)($row->user_id ?? 0),
                    'name' => $row->user_name ?? 'Unknown',
                    'email' => $row->user_email ?? '',
                    'role' => 'candidate',
                ],
                'position' => [
                    'id' => (int)($row->position_id ?? 0),
                    'name' => $row->position_name ?? 'Unknown Position',
                ],
                'election' => [
                    'id' => (int)($row->election_id ?? 0),
                    'title' => $row->election_title ?? 'Unknown Election',
                ],
            ];
        });

        \Log::info('Candidates list fetched', [
            'count' => $candidates->count(),
            'first_candidate_user' => $candidates->count() > 0 ? $candidates->first()['user'] : null
        ]);

        // Get elections and positions for filters from optimized view
        $elections = DB::table('view_election_statistics')->orderBy('title')->get(['id', 'title']);
        $positions = DB::table('positions')->orderBy('name')->get(['id', 'name', 'election_id']);

        return Inertia::render('admin/candidates', [
            'candidates' => $candidates,
            'elections' => $elections,
            'positions' => $positions,
            'filters' => $request->only(['search', 'election_id', 'position_id', 'partylist', 'course', 'year_level']),
        ]);
    }

    public function store(Request $request)
    {
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

        // Pre-fetch election and position for the email before the DB call
        $election = DB::table('elections')->where('id', $validated['election_id'])->first();
        $position = DB::table('positions')->where('id', $validated['position_id'])->first();

        if (!$election || !$position) {
             return redirect()->back()->withErrors(['error' => 'Invalid election or position selected.']);
        }

        try {
            // Generate unique password in PHP
            $generatedPassword = $this->generateUniquePassword($validated['name']);

            // Handle photo upload in PHP
            $photoName = time() . '_' . $request->file('photo')->getClientOriginalName();
            $request->file('photo')->storeAs('candidates', $photoName, 'public');

            // Call the stored procedure to handle the atomic DB inserts
            DB::statement('CALL sp_CreateCandidate(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $validated['name'],
                $validated['email'],
                Hash::make($generatedPassword),
                $validated['election_id'],
                $validated['position_id'],
                $validated['partylist'],
                $validated['platform'],
                $photoName,
                $validated['course'],
                $validated['year_level'],
                $validated['section'] ?? ''
            ]);

            // Send email with credentials
            try {
                Mail::to($validated['email'])->send(new CandidateCredentialsMail(
                    $validated['name'],
                    $validated['email'],
                    $generatedPassword,
                    $election->title,
                    $position->name
                ));
            } catch (\Exception $mailException) {
                \Log::error('Failed to send credentials email', [
                    'error' => $mailException->getMessage()
                ]);
            }

            // Redirect back with flash data for Inertia to catch
            return redirect()->back()->with([
                'success' => 'Candidate created successfully.',
                'generated_password' => $generatedPassword,
            ]);

        } catch (\PDOException $e) {
            if (isset($photoName)) {
                Storage::disk('public')->delete('candidates/' . $photoName);
            }

            return redirect()->back()->withErrors([
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        } catch (\Exception $e) {
            if (isset($photoName)) {
                Storage::disk('public')->delete('candidates/' . $photoName);
            }

            return redirect()->back()->withErrors([
                'error' => 'Failed to create candidate: ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, $id)
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
            $candidate = DB::table('candidates')->where('id', $id)->first();
            if (!$candidate) {
                return redirect()->back()->withErrors(['error' => 'Candidate not found.']);
            }

            // Handle photo logic in PHP (DB cannot delete files)
            $photoName = $candidate->photo;
            if ($request->hasFile('photo')) {
                // Delete old photo
                if ($candidate->photo) {
                    Storage::disk('public')->delete('candidates/' . $candidate->photo);
                }

                // Upload new photo
                $photoName = time() . '_' . $request->file('photo')->getClientOriginalName();
                $request->file('photo')->storeAs('candidates', $photoName, 'public');
            }

            // Call the stored procedure sp_UpdateCandidate
            DB::statement('CALL sp_UpdateCandidate(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $id,
                $validated['name'],
                $validated['election_id'],
                $validated['position_id'],
                $validated['partylist'],
                $validated['platform'],
                $photoName,
                $validated['course'],
                $validated['year_level'],
                $validated['section'] ?? ''
            ]);

            return redirect()->back()->with('success', 'Candidate updated successfully.');

        } catch (\Exception $e) {
            // If procedure fails and we just uploaded a NEW photo, delete it to stay clean
            if ($request->hasFile('photo') && isset($photoName)) {
                Storage::disk('public')->delete('candidates/' . $photoName);
            }

            return redirect()->back()->withErrors([
                'error' => 'Failed to update candidate: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $candidate = DB::table('candidates')->where('id', $id)->first();
            if (!$candidate) {
                return redirect()->back()->withErrors(['error' => 'Candidate not found.']);
            }

            // 1. Store the photo path before we delete the record
            $photoPath = 'candidates/' . $candidate->photo;

            // 2. Call the stored procedure sp_DeleteCandidate
            // This handles deleting the candidate AND the user record atomically
            DB::statement('CALL sp_DeleteCandidate(?)', [$id]);

            // 3. If the DB call was successful, now we delete the photo from disk
            if ($candidate->photo) {
                Storage::disk('public')->delete($photoPath);
            }

            return redirect()->back()->with('success', 'Candidate deleted successfully.');

        } catch (\Exception $e) {
            // If DB blocks deletion (e.g. because of foreign key constraints like existing votes)
            // we catch the error here and the photo remains safe on disk.
            return redirect()->back()->withErrors([
                'error' => 'Failed to delete candidate. They may already have votes cast for them. Error: ' . $e->getMessage()
            ]);
        }
    }
}


