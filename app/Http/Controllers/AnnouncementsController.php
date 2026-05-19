<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnnouncementsController extends Controller
{
    public function index()
    {
        $announcements = DB::table('announcements')
            ->join('users', 'announcements.created_by', '=', 'users.id')
            ->select('announcements.*', 'users.name as creator_name')
            ->orderBy('created_at', 'desc')
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

        $userId = auth()->id();
        
        // Use stored procedure sp_CreateAnnouncement
        DB::statement('CALL sp_CreateAnnouncement(?, ?, ?, ?)', [
            $validated['title'],
            $validated['content'],
            $validated['audience'],
            $userId
        ]);

        // If is_published was true, we need an extra update since the procedure doesn't handle it
        if ($validated['is_published'] ?? false) {
             DB::table('announcements')
                ->where('title', $validated['title'])
                ->where('created_by', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(1)
                ->update([
                    'is_published' => 1,
                    'published_at' => now()
                ]);
        }

        return redirect()->back()->with('success', 'Announcement created successfully');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'audience' => 'required|in:all,voters,candidates',
        ]);

        DB::table('announcements')->where('id', $id)->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'audience' => $validated['audience'],
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Announcement updated successfully');
    }

    public function destroy($id)
    {
        DB::table('announcements')->where('id', $id)->delete();

        return redirect()->back()->with('success', 'Announcement deleted successfully');
    }

    public function publish($id)
    {
        DB::table('announcements')->where('id', $id)->update([
            'is_published' => 1,
            'published_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Announcement published');
    }

    public function unpublish($id)
    {
        DB::table('announcements')->where('id', $id)->update([
            'is_published' => 0,
            'published_at' => null,
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Announcement unpublished');
    }

    public function voterIndex()
    {
        // Get published announcements for voters (audience: all or voters)
        $announcements = DB::table('announcements')
            ->join('users', 'announcements.created_by', '=', 'users.id')
            ->select('announcements.*', 'users.name as creator_name')
            ->where('is_published', true)
            ->whereIn('audience', ['all', 'voters'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('voter/announcement', [
            'announcements' => $announcements,
        ]);
    }
}
