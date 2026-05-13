<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Position;
use App\Models\Election;
use Illuminate\Support\Facades\DB;


class PositionController extends Controller
{
    public function position() {
        $positions = Position::with('election')
            ->withCount('candidates')
            ->latest()
            ->get();
        $elections = Election::all();
        
        return Inertia::render('admin/position', [
            'positions' => $positions,
            'elections' => $elections
        ]);
    }

    public function store(Request $request) {
        try {
            $validated = $request->validate([
                'election_id' => 'required|exists:elections,id',
                'name' => 'required|string|max:255',
                'max_selection' => 'required|integer|min:1',
            ]);

            // Call the stored procedure sp_CreatePosition
            DB::statement('CALL sp_CreatePosition(?, ?, ?)', [
                $validated['election_id'],
                $validated['name'],
                $validated['max_selection']
            ]);

            return redirect()->back()->with('success', 'Position created successfully');
        } catch (\Exception $e) {
            \Log::error('Position creation error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to create position.']);
        }
    }

    public function update(Request $request, Position $position) {
        try {
            $validated = $request->validate([
                'election_id' => 'required|exists:elections,id',
                'name' => 'required|string|max:255',
                'max_selection' => 'required|integer|min:1',
            ]);

            // Call the stored procedure sp_UpdatePosition
            DB::statement('CALL sp_UpdatePosition(?, ?, ?)', [
                $position->id,
                $validated['name'],
                $validated['max_selection']
            ]);

            return redirect()->back()->with('success', 'Position updated successfully');
        } catch (\Exception $e) {
            \Log::error('Position update error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to update position.']);
        }
    }

    public function destroy(Position $position) {
        try {
            // Note: Ensure you have sp_DeletePosition created, 
            // or use sp_DeleteElection logic. Since it's a simple delete:
            DB::statement('DELETE FROM positions WHERE id = ?', [$position->id]);

            return redirect()->back()->with('success', 'Position deleted successfully');
        } catch (\Exception $e) {
            \Log::error('Position deletion error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to delete position.']);
        }
    }
}
