<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;


class PositionController extends Controller
{
    public function position() {
        // Use the newly created view view_positions_details
        $positions = DB::table('view_positions_details')
            ->orderBy('election_title')
            ->orderBy('name')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'max_selection' => (int)$row->max_selection,
                    'election_id' => (int)$row->election_id,
                    'election' => [
                        'id' => (int)$row->election_id,
                        'title' => $row->election_title,
                        'description' => $row->election_description,
                        'is_active' => (bool)$row->election_is_active,
                    ],
                    'candidates_count' => $row->candidates_count,
                    'created_at' => $row->created_at,
                ];
            });
        
        // Fetch all elections using DB facade for the create/edit dropdowns
        $elections = DB::table('elections')->get()->map(function($e) {
            return [
                'id' => $e->id,
                'title' => $e->title,
                'description' => $e->description,
                'is_active' => (bool)$e->is_active,
            ];
        });
        
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

    public function update(Request $request, $id) {
        try {
            $validated = $request->validate([
                'election_id' => 'required|exists:elections,id',
                'name' => 'required|string|max:255',
                'max_selection' => 'required|integer|min:1',
            ]);

            // Call the stored procedure sp_UpdatePosition
            DB::statement('CALL sp_UpdatePosition(?, ?, ?)', [
                $id,
                $validated['name'],
                $validated['max_selection']
            ]);

            return redirect()->back()->with('success', 'Position updated successfully');
        } catch (\Exception $e) {
            \Log::error('Position update error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to update position.']);
        }
    }

    public function destroy($id) {
        try {
            // Call the stored procedure sp_DeletePosition
            DB::statement('CALL sp_DeletePosition(?)', [$id]);

            return redirect()->back()->with('success', 'Position deleted successfully');
        } catch (\Exception $e) {
            \Log::error('Position deletion error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to delete position.']);
        }
    }
}
