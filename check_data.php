<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Database Tables ===\n\n";

// Check approved_students
echo "APPROVED STUDENTS:\n";
$approved = \App\Models\ApprovedStudent::all();
echo "Count: " . $approved->count() . "\n";
foreach ($approved as $student) {
    echo "  - {$student->student_id}: {$student->name} | Course: {$student->course} | Year: {$student->year_level} | Section: " . ($student->section ?? 'NULL') . "\n";
}

echo "\nUSERS:\n";
$users = \App\Models\User::all();
echo "Count: " . $users->count() . "\n";
foreach ($users as $user) {
    echo "  - ID {$user->id}: {$user->name} ({$user->email}) | Role: {$user->role}\n";
}

echo "\nVOTER PROFILES:\n";
$profiles = \App\Models\VoterProfile::all();
echo "Count: " . $profiles->count() . "\n";
foreach ($profiles as $profile) {
    echo "  - User ID {$profile->user_id}: Student {$profile->student_id} | Course: {$profile->course} | Year: {$profile->year_level} | Section: {$profile->section}\n";
}

echo "\n=== Done ===\n";
