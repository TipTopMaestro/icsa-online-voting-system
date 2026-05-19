# 🗳️ ICSA Online Voting System - Database Refactoring Summary

This document outlines the transition of the application's business logic from the Laravel application layer (Eloquent ORM) to the MySQL database layer (Views & Stored Procedures).

---

## ✅ Completed Refactoring

### 1. Architectural Shift
- **Eloquent Removal:** Successfully deleted all physical Eloquent model files (`Candidate`, `Position`, `Election`, `Vote`, `Announcement`, `VoterProfile`, `ApprovedStudent`) from `app/Models/`.
- **DB Facade Integration:** All 11 controllers in `app/Http/Controllers/` have been refactored to use the `Illuminate\Support\Facades\DB` facade.
- **Model Compatibility:** `app/Models/User.php` was retained for core Laravel Authentication/Fortify compatibility, but its usage in custom logic was replaced with raw DB queries.

### 2. Implemented Logic via SQL Views
The application now relies on these optimized views for read operations:
- `view_candidates_details`: Provides candidate information joined with user, election, and position data.
- `view_candidate_dashboard`: Aggregate view for candidate-specific statistics, rankings, and election info.
- `view_election_results`: Pre-calculated vote counts, percentages, and rankings per position.
- `view_election_statistics`: High-level summary of election status, turnout, and candidate/position counts.
- `view_voter_details`: Comprehensive voter profiles with active election voting status.

### 3. Implemented Logic via Stored Procedures
Atomic operations and complex writes are now handled by:
- `sp_RegisterVoter`: Atomic whitelist checking and double-insert into `users` and `voter_profiles`.
- `sp_CreateCandidate`: Handles user creation, role assignment, and candidate record creation in one transaction.
- `sp_CastBallot`: Processes multi-position voting logic and JSON-based vote batching.
- `sp_CreateElection` / `sp_UpdateElection` / `sp_DeleteElection`: Standardized election management.
- `sp_ActivateElection` / `sp_DeactivateElection`: Handles the logic for toggling election states.
- `sp_CreateAnnouncement`: Standardized announcement creation.

---

## 🚀 New Business Logic Flow

### Data Retrieval (The "Flat to Nested" Mapper)
To maintain compatibility with the Vue 3 frontend while using flat SQL results, the controllers now implement a **Through-Mapping** pattern:
```php
$data = DB::table('view_example')->paginate(15)->through(function ($row) {
    return [
        'id' => $row->id,
        'nested_object' => [
            'key' => $row->flat_column_from_view
        ]
    ];
});
```

### Write Operations
All data modification now follows the **Procedure-First** pattern:
1. Validate input in PHP (request validation).
2. Execute PHP logic for non-DB tasks (e.g., File Uploads, Email Sending).
3. Invoke Stored Procedure for all DB changes via `DB::statement()`.

---

## 🛠️ Future Improvements & Roadmap

### 1. Pending Stored Procedures (To be implemented)
- `sp_DeletePosition`: Currently using raw SQL; needs a procedure to safely handle cascade deletions or movement of candidates.
- `sp_UpdateCandidatePhoto`: Move the logic of updating the photo path into a procedure to keep `updated_at` sync consistent.
- `sp_BulkRegisterStudents`: A procedure to handle bulk student imports from temporary tables.
- `sp_ArchiveElection`: A procedure to move ended election data to a history/archive table to keep active tables small.

### 2. Enhanced Views (To be implemented)
- `view_audit_logs`: A view to aggregate admin activities from a future `audit_logs` table.
- `view_turnout_analytics`: Time-series view for real-time turnout graphing (voters per hour).
- `view_unvoted_students`: Quick lookup for students who have not yet participated in an active election.

### 3. Application Layer Improvements
- **Repository Pattern:** Wrap the `DB::table` calls into Repository classes to further decouple controllers from specific table names.
- **SQL Injection Guard:** Ensure all procedure calls continue to use bound parameters `?` instead of string interpolation.
- **Materialized Views:** For future scaling, implement triggers that update "result summary" tables to act as materialized views for the real-time leaderboard.

---
*Last Updated: May 13, 2026*
