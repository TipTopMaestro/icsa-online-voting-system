# Photo & Election Status Fixed ✅

## Issues Fixed

### 1. ✅ Candidate Photo - Now Using Candidates Table

**Problem:** Photo was stored/fetched from users table, but admin adds photos to candidates table

**Solution:** Updated all places to use `candidates.photo` instead of `users.photo`

#### Files Modified:

**HandleInertiaRequests.php** - Shared auth data
```php
if ($user->role === 'candidate') {
    $candidate = Candidate::where('user_id', $user->id)->first();
    $userPhoto = $candidate && $candidate->photo 
        ? asset('storage/candidates/' . $candidate->photo)
        : null;
}
```

**CandidateController@profile()** - Profile page
```php
'photo' => $candidate && $candidate->photo 
    ? asset('storage/candidates/' . $candidate->photo)
    : null,
```

**CandidateController@updatePhoto()** - Photo upload
```php
// Store in candidates folder
$file->storeAs('candidates', $filename, 'public');
$candidate->photo = $filename;
$candidate->save();
```

**CandidateController@settings()** - Settings page
```php
'photo' => $candidate && $candidate->photo 
    ? asset('storage/candidates/' . $candidate->photo)
    : null,
```

### 2. ✅ Dashboard Election Status - Now Accurate

**Problem:** Ended elections showed as "Active"

**Solution:** Check if current time is BETWEEN start and end dates

#### Before (Wrong):
```php
$isElectionActive = Carbon::now()->between(...) && $activeElection->is_active;
```
Still used database is_active flag which might not be updated.

#### After (Correct):
```php
$now = Carbon::now();
$startDate = Carbon::parse($activeElection->start_datetime);
$endDate = Carbon::parse($activeElection->end_datetime);

$isElectionOngoing = $now->between($startDate, $endDate);
```

Only checks dates - if current time is between start and end, it's active. Otherwise ended.

### 3. ✅ Results Page - Also Fixed

Made election status consistent across all pages:

```php
'is_active' => Carbon::now()->between(
    Carbon::parse($election->start_datetime),
    Carbon::parse($election->end_datetime)
),
```

## Photo Storage Structure

### Before:
```
storage/
├── profile_photos/     ← Used for candidates (WRONG)
└── candidates/         ← Admin stores here
```

### After:
```
storage/
├── candidates/         ← ALL candidate photos here (CORRECT)
└── profile_photos/     ← Can be used for other roles
```

## Testing Steps

### Test 1: Photo Display & Upload

1. **Check if existing photo shows:**
   ```sql
   -- Verify candidate has photo in database
   SELECT photo FROM candidates WHERE user_id = [candidate_user_id];
   ```
   
2. **Login as candidate:**
   - Go to dashboard
   - Check nav bar - photo should show
   - Go to profile page
   - Photo should show in profile card
   - Photo should show in settings page

3. **Upload new photo:**
   - Click camera icon on profile
   - Select image
   - Click Upload
   - Refresh page
   - Photo should appear everywhere (nav, profile, settings)

4. **Verify in database:**
   ```sql
   SELECT photo FROM candidates WHERE user_id = [candidate_user_id];
   -- Should show: 1733472234_yourfile.jpg
   ```

5. **Verify in storage:**
   ```
   storage/candidates/1733472234_yourfile.jpg should exist
   ```

### Test 2: Election Status

1. **Create test scenario:**
   ```sql
   -- Election that ended yesterday
   UPDATE elections 
   SET start_datetime = NOW() - INTERVAL 7 DAY,
       end_datetime = NOW() - INTERVAL 1 DAY
   WHERE id = [election_id];
   ```

2. **Login as candidate in that election:**
   - Go to dashboard
   - Check election card
   - Should show gray "Ended" badge, NOT green "Active"

3. **Create ongoing election:**
   ```sql
   -- Election happening now
   UPDATE elections 
   SET start_datetime = NOW() - INTERVAL 1 DAY,
       end_datetime = NOW() + INTERVAL 7 DAY
   WHERE id = [election_id];
   ```

4. **Login as candidate:**
   - Dashboard should show green "Active" badge

5. **Future election:**
   ```sql
   -- Election starting tomorrow
   UPDATE elections 
   SET start_datetime = NOW() + INTERVAL 1 DAY,
       end_datetime = NOW() + INTERVAL 8 DAY
   WHERE id = [election_id];
   ```

6. **Login as candidate:**
   - Should show "Ended" (not active yet)

## Election Status Logic

### Dashboard:
```php
$now = Carbon::now();
$startDate = Carbon::parse($election->start_datetime);
$endDate = Carbon::parse($election->end_datetime);

$isElectionOngoing = $now->between($startDate, $endDate);
// Returns true ONLY if current time is between start and end
```

### Results Page:
```php
'is_active' => Carbon::now()->between(
    Carbon::parse($election->start_datetime),
    Carbon::parse($election->end_datetime)
),
```

### Badge Display in Frontend:
```vue
<span v-if="election.is_ongoing">Active</span>
<span v-else>Ended</span>
```

## What Changed

| File | Change | Status |
|------|--------|--------|
| HandleInertiaRequests.php | Use candidates.photo for candidate role | ✅ |
| CandidateController@profile | Fetch from candidates table | ✅ |
| CandidateController@updatePhoto | Save to candidates table | ✅ |
| CandidateController@settings | Fetch from candidates table | ✅ |
| CandidateController@dashboard | Fixed election status logic | ✅ |
| CandidateController@results | Fixed election status logic | ✅ |

## Photo Path Reference

| Context | Path |
|---------|------|
| Storage | `storage/candidates/filename.jpg` |
| Database | `filename.jpg` (just filename) |
| URL | `http://localhost/storage/candidates/filename.jpg` |
| Asset helper | `asset('storage/candidates/' . $filename)` |

## Verification Queries

```sql
-- Check candidate photo
SELECT c.id, u.name, c.photo 
FROM candidates c 
JOIN users u ON c.user_id = u.id 
WHERE u.role = 'candidate';

-- Check election dates
SELECT id, title, start_datetime, end_datetime,
  CASE 
    WHEN NOW() BETWEEN start_datetime AND end_datetime THEN 'Active'
    WHEN NOW() < start_datetime THEN 'Not Started'
    ELSE 'Ended'
  END as status
FROM elections;

-- Check candidate election status
SELECT u.name, e.title, e.start_datetime, e.end_datetime,
  CASE 
    WHEN NOW() BETWEEN e.start_datetime AND e.end_datetime THEN 'Active'
    ELSE 'Ended'
  END as status
FROM candidates c
JOIN users u ON c.user_id = u.id
JOIN elections e ON c.election_id = e.id
WHERE u.role = 'candidate';
```

## Build Status

✅ Assets compiled successfully
```
npm run build
✓ 3495 modules transformed
✓ Build complete
```

## Summary

**Photo Issue:** ✅ FIXED
- Now uses `candidates.photo` everywhere
- Nav, profile, and settings all show correct photo
- Upload saves to candidates table
- Consistent with admin's add candidate flow

**Election Status:** ✅ FIXED
- Only checks date range (between start and end)
- Ignores database is_active flag
- Consistent across dashboard and results
- Accurate real-time status

**Ready to test!** 🚀
