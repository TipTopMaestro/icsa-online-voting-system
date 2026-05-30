# 🗄️ ICSA OVS Database Documentation

This document provides a comprehensive overview of the database objects defined in the ICSA Online Voting System, categorizing them by utilization and explaining their purpose within the application logic.

---

## 🏗️ Stored Procedures

Specialized SQL routines that handle atomic operations, transactions, and complex business logic inside the database.

### ✅ Utilized Procedures
These are actively called by the Laravel application (Controllers and Actions).

| Procedure | Purpose | Utilization |
| :--- | :--- | :--- |
| `sp_CastBallot` | Processes the JSON-based voting transaction, validating active status and ensuring one vote per user/election. | `VotingController` |
| `sp_RegisterVoter` | Securely registers a voter by checking the `approved_students` whitelist and creating linked records in `users` and `voter_profiles`. | `RegisterUser` (Fortify Action) |
| `sp_GetElectionStatistics` | Aggregates high-level metrics (turnout, counts) for all elections. | `ElectionController` |
| `sp_ActivateElection` | Toggles an election to 'Live' status and deactivates others. | `ElectionController` |
| `sp_DeactivateElection` | Forcefully ends an active election. | `ElectionController` |
| `sp_CreateCandidate` | Atomically creates a new user account with the 'candidate' role and their associated candidate profile. | `CandidatesController` |
| `sp_UpdateCandidate` | Synchronizes updates between the `users` and `candidates` tables for a nominee. | `CandidatesController` |
| `sp_DeleteCandidate` | Safely removes a candidate and their linked user account. | `CandidatesController` |
| `sp_CreateElection` | Initializes a new electoral system. | `ElectionController` |
| `sp_UpdateElection` | Updates the title, description, and timeline of an election. | `ElectionController` |
| `sp_DeleteElection` | Removes an electoral system and cascades associated data. | `ElectionController` |
| `sp_CreatePosition` | Defines a new role (e.g., President) within an election. | `PositionController` |
| `sp_UpdatePosition` | Modifies position names and max selection counts. | `PositionController` |
| `sp_DeletePosition` | Safely removes a position from the system. | `PositionController` |
| `sp_UpdateUserProfile` | Synchronized update of user info and voter profile details. | `ProfileController` |
| `sp_CreateAnnouncement` | Creates and automatically publishes a new system announcement. | `AnnouncementsController` |
| `sp_UpdateAnnouncement` | Modifies existing announcement content and target audience. | `AnnouncementsController` |
| `sp_DeleteAnnouncement` | Removes an announcement from the system. | `AnnouncementsController` |
| `sp_PublishAnnouncement` | Manually pushes an announcement to the live feed. | `AnnouncementsController` |
| `sp_UnpublishAnnouncement` | Hides an announcement from the feed. | `AnnouncementsController` |
| `sp_UpdateCandidatePhoto` | Updates the profile portrait path for a candidate. | `CandidateController` |
| `sp_UpdateCandidatePlatform` | Updates a candidate's campaign statement. | `CandidateController` |
| `sp_UpdateCandidateProfile` | Updates basic user info (name/email) for a candidate. | `CandidateController` |
| `sp_UpdateUserPassword` | Securely resets a user's hashed password. | `CandidateController` |

### ❌ Unutilized Procedures
These exist in the schema but are not currently called by the application (logic is either handled via raw SQL or redundant).

| Procedure | Reason for Non-use |
| :--- | :--- |
| `sp_GetAllPositions` | The application currently uses direct queries on `view_positions_details` or standard Eloquent patterns. |

---

## 📊 Database Views

Optimized virtual tables used for real-time reporting, dashboards, and simplifying complex multi-table joins.

### ✅ Utilized Views
These views are queried like standard tables using `DB::table('view_name')`.

| View | Purpose | Utilization |
| :--- | :--- | :--- |
| `view_election_statistics` | Pre-calculates turnout percentages, valid vote counts, and candidate/position densities. | `ElectionController`, `ResultController`, `DashboardController` |
| `view_election_results` | Real-time calculation of vote counts, percentages, and rankings per position using window functions. | `ResultController`, `DashboardController`, `CandidateController` |
| `view_candidates_details` | Joins candidates with user accounts and position names for administrative listings. | `CandidatesController`, `VotingController` |
| `view_candidate_dashboard` | Comprehensive aggregate view for the Candidate's personal dashboard (rank, standing, and stats). | `CandidateController` |
| `view_voter_details` | Merges user account data with voter profile details and active voting status. | `VotersController`, `ProfileController` |
| `view_positions_details` | Lists roles per election with candidate counts and active status indicators. | `PositionController` |
| `view_recent_votes` | A time-ordered feed of votes cast for the Admin activity monitor. | `DashboardController` |
| `view_voting_receipt` | Aggregates cast ballots into a readable format for the Voter's receipt page. | `VotingController` |

### ❌ Unutilized Views

| View | Reason for Non-use |
| :--- | :--- |
| `view_auth_users` | Originally intended for custom auth; the system currently uses the `User` model with direct joins in `HandleInertiaRequests`. |

---

## ⚡ Triggers

Automatic actions triggered by database events to maintain data consistency and performance.

| Trigger | Event | Action |
| :--- | :--- | :--- |
| `trg_UpdateVoteCount` | `AFTER INSERT` on `votes` | Increments the `votes_count` column in the `candidates` table automatically. This ensures real-time results without expensive `COUNT()` queries. |

---

## 🔑 Constraints & Indexes

Essential for maintaining data integrity and query performance.

### Foreign Key Constraints
Ensures relational integrity and handles cascading deletions. All 11 keys are utilized to enforce referential integrity.
*   **`votes`**: Linked to `users`, `elections`, `candidates`, and `positions`. (DELETE CASCADE)
*   **`candidates`**: Linked to `users`, `elections`, and `positions`. (DELETE CASCADE)
*   **`voter_profiles`**: Linked to `users`. (DELETE CASCADE)
*   **`announcements`**: Linked to `users` (author). (DELETE CASCADE)

### Specialized Indexes
*   **`users.email` (UNIQUE)**: Primary index for authentication lookup.
*   **`approved_students.student_id` (UNIQUE)**: Ensures whitelist integrity.
*   **`voter_profiles.student_id` (UNIQUE)**: Prevents duplicate profile creation.
*   **`announcements.is_published`**: Optimized for filtering the public feed.
*   **`sessions.user_id`**: Performance index for active session tracking.
