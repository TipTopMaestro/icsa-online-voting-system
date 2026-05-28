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

        // Use stored procedure sp_UpdateAnnouncement
        DB::statement('CALL sp_UpdateAnnouncement(?, ?, ?, ?)', [
            $id,
            $validated['title'],
            $validated['content'],
            $validated['audience']
        ]);

        return redirect()->back()->with('success', 'Announcement updated successfully');
    }

    public function destroy($id)
    {
        // Use stored procedure sp_DeleteAnnouncement
        DB::statement('CALL sp_DeleteAnnouncement(?)', [$id]);

        return redirect()->back()->with('success', 'Announcement deleted successfully');
    }

    public function publish($id)
    {
        // Use stored procedure sp_PublishAnnouncement
        DB::statement('CALL sp_PublishAnnouncement(?)', [$id]);

        return redirect()->back()->with('success', 'Announcement published');
    }

    public function unpublish($id)
    {
        // Use stored procedure sp_UnpublishAnnouncement
        DB::statement('CALL sp_UnpublishAnnouncement(?)', [$id]);

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
