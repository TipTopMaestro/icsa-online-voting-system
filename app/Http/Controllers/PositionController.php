<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Position;
use App\Models\Election;


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
        $validated = $request->validate([
            'election_id' => 'required|exists:elections,id',
            'name' => 'required|string|max:255',
            'max_selection' => 'required|integer|min:1',
        ]);

        Position::create($validated);

        return redirect()->back()->with('success', 'Position created successfully');
    }

    public function update(Request $request, Position $position) {
        $validated = $request->validate([
            'election_id' => 'required|exists:elections,id',
            'name' => 'required|string|max:255',
            'max_selection' => 'required|integer|min:1',
        ]);

        $position->update($validated);

        return redirect()->back()->with('success', 'Position updated successfully');
    }

    public function destroy(Position $position) {
        $position->delete();

        return redirect()->back()->with('success', 'Position deleted successfully');
    }
}
