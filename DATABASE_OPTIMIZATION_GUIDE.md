# Database Optimization Technical Guide: ICSA Online Voting System

This guide outlines the technical implementation of advanced database techniques to shift business logic from the application layer (Laravel) to the database layer (MySQL). This transition improves performance, ensures data atomicity, and maintains high integrity during election events.

---

## 🏗️ 1. Stored Procedures: Moving Logic to the Engine

### A. Voting Transaction (`sp_CastBallot`)
**Target Controller:** `VotingController@store`
*   **Implementation:** A procedure that accepts `user_id`, `election_id`, and a JSON array of `candidate_ids`.
*   **Logic:** 
    *   Verify the election is active.
    *   Verify the user hasn't voted.
    *   Insert multiple rows into the `votes` table using a loop.
    *   Update the `candidates.votes_count` column.
*   **Why:** Ensures that a ballot is either 100% saved or 100% rejected (Atomicity).

### B. Secure Registration (`sp_RegisterVoter`)
**Target Controller:** `RegisteredUserController@store`
*   **Implementation:** Accepts student details and handles the logic for checking the `approved_students` whitelist.
*   **Logic:** If the student exists in the whitelist, it performs a double-insert into `users` and `voter_profiles`.
*   **Why:** Guarantees that no `User` account exists without a corresponding `VoterProfile`.

---

## ⚡ 2. Triggers: Automating Data Integrity

### A. Vote Counter (`after_vote_insert`)
**Target Table:** `votes`
*   **Logic:** Every time a new row is inserted into `votes`, the trigger automatically updates the `votes_count` column in the `candidates` table.
*   **Why:** Eliminates the need for manual `increment()` calls in PHP, ensuring the counts are always accurate even if votes are inserted via raw SQL.

### B. User Cleanup (`before_user_delete`)
**Target Table:** `users`
*   **Logic:** Before a user is deleted, this trigger can log the deletion or move sensitive data to an audit table for security purposes.

---

## 📊 3. Database Views: Real-Time Analytics

### A. `view_election_results`
**Target Controller:** `ResultController`
*   **Logic:** Pre-calculates candidate standings, total votes, and percentages.
*   **SQL Feature:** Uses `RANK() OVER (PARTITION BY position_id ORDER BY votes_count DESC)` to determine winners dynamically.
*   **Why:** Reduces complex PHP math; the controller simply runs `SELECT * FROM view_election_results`.

### B. `view_voter_turnout`
**Target Controller:** `DashboardController`
*   **Logic:** Joins the `voter_profiles` and `votes` tables to show real-time participation rates per course/year-level.

---

## 🚀 4. Indexing Strategy: Eliminating Bottlenecks

| Table | Column(s) | Index Type | Purpose |
| :--- | :--- | :--- | :--- |
| `votes` | `(user_id, election_id)` | **Composite Unique** | Prevents duplicate voting and speeds up status checks. |
| `approved_students` | `student_id` | **Unique Index** | Instant whitelist verification during registration. |
| `candidates` | `position_id` | **Index** | Faster loading of ballot pages for voters. |
| `elections` | `is_active`, `end_datetime` | **Index** | Faster identification of ongoing elections. |

---

## 🔗 5. Foreign Key Constraints: The Safety Net

*   **`positions.election_id` → `elections.id` (`ON DELETE CASCADE`)**: Automatically deletes positions if an election is wiped.
*   **`candidates.position_id` → `positions.id` (`ON DELETE CASCADE`)**: Ensures no "orphaned" candidates remain if a position is removed.
*   **`votes.candidate_id` → `candidates.id` (`ON DELETE RESTRICT`)**: Prevents an admin from deleting a candidate who has already received votes, protecting election history.

---

## 📈 Summary Mapping

| Controller | Techniques to Apply |
| :--- | :--- |
| **VotingController** | Stored Procedures (`sp_CastBallot`), Composite Indexing. |
| **ResultController** | Database Views (`view_election_results`), Rank Functions. |
| **RegisteredUserController** | Stored Procedures (`sp_RegisterVoter`), Unique Indexing. |
| **DashboardController** | Database Views (`view_voter_turnout`), Aggregation Optimization. |
| **ElectionController** | Foreign Key Cascades, Date-time Indexing. |

---
*Implementation Note: These changes should be applied via Laravel Migrations using `DB::unprepared()` to ensure they are version-controlled alongside the application code.*
