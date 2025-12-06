# Candidate Module - Quick Reference

## Routes
All candidate routes are protected by `auth`, `verified`, and `candidate` middleware.

| Method | URL | Controller Method | Purpose |
|--------|-----|-------------------|---------|
| GET | `/candidate/dashboard` | `CandidateController@dashboard` | Main dashboard |
| GET | `/candidate/profile` | `CandidateController@profile` | Profile page (empty) |
| GET | `/candidate/announcements` | `CandidateController@announcements` | View announcements |
| GET | `/candidate/results` | `CandidateController@results` | View results |
| GET | `/candidate/settings` | `CandidateController@settings` | Settings (empty) |

## Testing the Module

### 1. Login as Candidate
- Login with a user account that has `role = 'candidate'`
- After login, candidates should be redirected to `/candidate/dashboard`

### 2. Verify Candidate Record
Make sure the candidate has a record in the `candidates` table:
```sql
SELECT * FROM candidates WHERE user_id = [candidate_user_id];
```

If not, create one:
```sql
INSERT INTO candidates (user_id, election_id, position_id, partylist, created_at, updated_at)
VALUES (
    [user_id],
    [active_election_id],
    [position_id],
    'Independent',
    NOW(),
    NOW()
);
```

### 3. Test Each Page

**Dashboard** (`/candidate/dashboard`)
- ✅ Shows active election name and description
- ✅ Displays position running for
- ✅ Vote count received
- ✅ Current ranking among position candidates
- ✅ Total candidates in position
- ✅ Vote percentage
- ✅ Recent announcements (last 3)
- ✅ Performance overview bar

**Profile** (`/candidate/profile`)
- ✅ Shows empty placeholder
- ⏳ Ready for custom design

**Announcements** (`/candidate/announcements`)
- ✅ Lists all published announcements
- ✅ Shows title, content, and timestamp
- ✅ Empty state if no announcements

**Results** (`/candidate/results`)
- ✅ Shows results grouped by position
- ✅ Displays vote counts
- ✅ Highlights winner (top candidate)
- ✅ Shows candidate photos and partylist

**Settings** (`/candidate/settings`)
- ✅ Shows empty placeholder
- ⏳ Ready for custom design

## Dashboard Data Structure

The dashboard returns the following data:

```typescript
{
    user: {
        name: string;
        photo: string | null;
    },
    activeElection: {
        id: number;
        name: string;
        description: string;
        start_datetime: string;
        end_datetime: string;
        is_ongoing: boolean;
    } | null,
    candidatePosition: {
        id: number;
        name: string;
    } | null,
    recentAnnouncements: [
        {
            id: number;
            title: string;
            content: string;
            created_at: string; // human readable
        }
    ],
    statistics: {
        votesReceived: number;
        totalVoters: number;
        votePercentage: number;
        ranking: number;
        totalCandidates: number;
    }
}
```

## Navigation Structure

### Desktop Navigation
```
Logo | ICSA OVS | Dashboard | Profile | Announcements | Results | [User Avatar ▼]
```

### Dropdown Menu
```
- My Profile
- Settings
- Logout
```

### Mobile Navigation
```
☰ Menu
├── Dashboard
├── Profile
├── Announcements
└── Results
```

## Middleware Chain

```
Route → auth → verified → candidate → CandidateController
```

The `candidate` middleware checks if `Auth::user()->role === 'candidate'`

## Common Issues & Solutions

### 1. "No candidate record found"
**Problem:** User has role 'candidate' but no record in candidates table  
**Solution:** Create a candidate record linked to the user

### 2. "Statistics show 0"
**Problem:** No votes cast yet  
**Solution:** Normal - statistics will update as votes come in

### 3. "Ranking shows 0"
**Problem:** Candidate not found in position candidates list  
**Solution:** Verify election_id and position_id are correct

### 4. "Can't access pages"
**Problem:** User role is not 'candidate'  
**Solution:** Update user role: `UPDATE users SET role = 'candidate' WHERE id = [id]`

## Database Requirements

### Required Tables
- `users` (with role column)
- `candidates`
- `elections`
- `positions`
- `votes`
- `announcements`

### Candidate Record Requirements
```sql
candidates
├── user_id (FK to users)
├── election_id (FK to elections)
├── position_id (FK to positions)
├── partylist (optional)
└── photo (optional)
```

## Next Steps

1. **Profile Page**
   - Add candidate information form
   - Photo upload
   - Platform editing
   - Course, year level, section details

2. **Settings Page**
   - Password change
   - Email preferences
   - Notification settings

3. **Enhanced Dashboard**
   - Vote trends chart
   - Hourly/daily vote breakdown
   - Voter demographics

4. **Notifications**
   - Real-time vote updates
   - New announcements alerts
   - Election status changes

## Files to Customize

When adding custom designs to Profile and Settings:

### Profile Page
Edit: `resources/js/pages/candidate/Profile.vue`
- Add form fields for candidate info
- Implement photo upload
- Add platform editor
- Save to backend via POST/PUT request

### Settings Page
Edit: `resources/js/pages/candidate/Settings.vue`
- Add password change form
- Email notification toggles
- Account preferences
- Theme settings

## API Endpoints Pattern

If you need to add API endpoints for AJAX requests:

```php
// In routes/web.php or routes/api.php
Route::post('/candidate/profile/update', [CandidateController::class, 'updateProfile']);
Route::post('/candidate/profile/photo', [CandidateController::class, 'updatePhoto']);
Route::put('/candidate/settings', [CandidateController::class, 'updateSettings']);
```

## Success Checklist

- ✅ CandidateLayout created with navigation
- ✅ Dashboard with vote statistics
- ✅ Profile page (empty placeholder)
- ✅ Announcements page
- ✅ Results page
- ✅ Settings page (empty placeholder)
- ✅ CandidateController with all methods
- ✅ Routes configured and working
- ✅ Middleware protection in place
- ✅ Frontend assets built successfully

## Build Commands

```bash
# Development
npm run dev

# Production
npm run build

# Check routes
php artisan route:list --path=candidate
```

## Support Notes

- Dashboard cards are responsive
- Mobile menu works on small screens
- Dark mode compatible (uses Tailwind dark: classes)
- Settings link only in dropdown (not main nav)
- Announcements and Results reuse voter view logic
- No Ziggy dependency (uses Inertia Link directly)
