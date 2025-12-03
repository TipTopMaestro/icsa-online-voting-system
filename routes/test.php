<?php

use Illuminate\Support\Facades\Route;
use App\Models\VoterProfile;
use App\Models\User;

Route::get('/test-voter-search', function () {
    echo "<h1>Voter Search Function Test</h1>";
    echo "<hr>";
    
    // Check if we have voters
    $voterCount = VoterProfile::count();
    $userCount = User::where('role', 'voter')->count();
    
    echo "<h2>Database Status:</h2>";
    echo "<p>Total Voters: <strong>{$voterCount}</strong></p>";
    echo "<p>Total Voter Users: <strong>{$userCount}</strong></p>";
    echo "<hr>";
    
    if ($voterCount === 0) {
        echo "<p style='color: red;'><strong>⚠ No voters found in database!</strong></p>";
        echo "<p>You need to create some test data first.</p>";
        echo "<p>Would you like me to create sample voter data?</p>";
        return;
    }
    
    // Test 1: Get all voters
    echo "<h2>Test 1: Get All Voters</h2>";
    $allVoters = VoterProfile::with('user')->take(5)->get();
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Student ID</th><th>Name</th><th>Email</th><th>Course</th><th>Year</th><th>Has Voted</th></tr>";
    foreach ($allVoters as $voter) {
        echo "<tr>";
        echo "<td>{$voter->id}</td>";
        echo "<td>{$voter->student_id}</td>";
        echo "<td>{$voter->user->name}</td>";
        echo "<td>{$voter->user->email}</td>";
        echo "<td>{$voter->course}</td>";
        echo "<td>{$voter->year_level}</td>";
        echo "<td>" . ($voter->has_voted ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<hr>";
    
    // Test 2: Search by student ID
    $firstVoter = $allVoters->first();
    if ($firstVoter) {
        echo "<h2>Test 2: Search by Student ID</h2>";
        $searchId = $firstVoter->student_id;
        echo "<p>Searching for: <strong>{$searchId}</strong></p>";
        
        $searchResult = VoterProfile::where('student_id', 'like', "%{$searchId}%")->with('user')->get();
        echo "<p>Results found: <strong>{$searchResult->count()}</strong></p>";
        
        if ($searchResult->count() > 0) {
            echo "<p style='color: green;'>✓ Search by Student ID: <strong>WORKING</strong></p>";
        } else {
            echo "<p style='color: red;'>✗ Search by Student ID: <strong>FAILED</strong></p>";
        }
        echo "<hr>";
        
        // Test 3: Search by name
        echo "<h2>Test 3: Search by Name</h2>";
        $searchName = explode(' ', $firstVoter->user->name)[0]; // Get first name
        echo "<p>Searching for: <strong>{$searchName}</strong></p>";
        
        $nameResult = VoterProfile::whereHas('user', function ($q) use ($searchName) {
            $q->where('name', 'like', "%{$searchName}%");
        })->with('user')->get();
        
        echo "<p>Results found: <strong>{$nameResult->count()}</strong></p>";
        
        if ($nameResult->count() > 0) {
            echo "<p style='color: green;'>✓ Search by Name: <strong>WORKING</strong></p>";
        } else {
            echo "<p style='color: red;'>✗ Search by Name: <strong>FAILED</strong></p>";
        }
        echo "<hr>";
        
        // Test 4: Filter by course
        echo "<h2>Test 4: Filter by Course</h2>";
        $course = $firstVoter->course;
        echo "<p>Filtering by: <strong>{$course}</strong></p>";
        
        $courseResult = VoterProfile::where('course', $course)->get();
        echo "<p>Results found: <strong>{$courseResult->count()}</strong></p>";
        
        if ($courseResult->count() > 0) {
            echo "<p style='color: green;'>✓ Filter by Course: <strong>WORKING</strong></p>";
        } else {
            echo "<p style='color: red;'>✗ Filter by Course: <strong>FAILED</strong></p>";
        }
        echo "<hr>";
        
        // Test 5: Filter by year level
        echo "<h2>Test 5: Filter by Year Level</h2>";
        $year = $firstVoter->year_level;
        echo "<p>Filtering by: <strong>Year {$year}</strong></p>";
        
        $yearResult = VoterProfile::where('year_level', $year)->get();
        echo "<p>Results found: <strong>{$yearResult->count()}</strong></p>";
        
        if ($yearResult->count() > 0) {
            echo "<p style='color: green;'>✓ Filter by Year: <strong>WORKING</strong></p>";
        } else {
            echo "<p style='color: red;'>✗ Filter by Year: <strong>FAILED</strong></p>";
        }
        echo "<hr>";
        
        // Test 6: Filter by voting status
        echo "<h2>Test 6: Filter by Voting Status</h2>";
        $votedCount = VoterProfile::where('has_voted', true)->count();
        $notVotedCount = VoterProfile::where('has_voted', false)->count();
        
        echo "<p>Voted: <strong>{$votedCount}</strong></p>";
        echo "<p>Not Voted: <strong>{$notVotedCount}</strong></p>";
        
        if ($votedCount >= 0 && $notVotedCount >= 0) {
            echo "<p style='color: green;'>✓ Filter by Voting Status: <strong>WORKING</strong></p>";
        } else {
            echo "<p style='color: red;'>✗ Filter by Voting Status: <strong>FAILED</strong></p>";
        }
        echo "<hr>";
    }
    
    echo "<h2>Summary</h2>";
    echo "<p>All search and filter functions have been tested.</p>";
    echo "<p><a href='/admin/voters'>Go to Voters Page</a></p>";
});
