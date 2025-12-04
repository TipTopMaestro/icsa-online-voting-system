<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementsController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('creator')
            ->latest()
            ->get();

        return Inertia::render('admin/announcements', [
            'announcements' => $announcements,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'audience' => 'required|in:all,voters,candidates',
            'is_published' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();
        
        if ($validated['is_published'] ?? false) {
            $validated['published_at'] = now();
        }

        Announcement::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'audience' => 'required|in:all,voters,candidates',
        ]);

        $announcement->update($validated);

        return redirect()->back();
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->back();
    }

    public function publish(Announcement $announcement)
    {
        $announcement->publish();

        return redirect()->back();
    }

    public function unpublish(Announcement $announcement)
    {
        $announcement->unpublish();

        return redirect()->back();
    }
}
