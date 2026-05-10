# ICSA Online Voting System - Session Summary & Documentation

This document provides a comprehensive overview of the analysis, documentation, and architectural recommendations generated during this engineering session for the **ICSA Online Voting System**.

---

## 📋 Table of Contents
1. [Project Overview](#project-overview)
2. [Architectural Analysis](#architectural-analysis)
3. [Inner Workings & Data Flow](#inner-workings--data-flow)
4. [Database Optimization Strategy](#database-optimization-strategy)
5. [Refactoring Recommendations](#refactoring-recommendations)

---

## 🏗️ Project Overview
The **ICSA Online Voting System** is a robust, multi-role web application designed for secure student elections.
*   **Backend:** Laravel (PHP)
*   **Frontend:** Vue.js 3 with Inertia.js (Single Page Application experience)
*   **Database:** MySQL / PostgreSQL
*   **Roles:** Admin, Voter, and Candidate.

---

## 🔍 Architectural Analysis
The system follows the **Active Record (Eloquent)** pattern, with logic primarily residing in the Controller layer. 

### Key Components:
*   **Whitelist Security:** Uses the `ApprovedStudent` model to restrict registration to eligible BSIT/BSIS students.
*   **Role-Based Access Control (RBAC):** Middleware (`CheckAdmin`, `CheckVoter`) secures routes.
*   **Inertia.js Bridge:** Eliminates the need for a separate REST API by passing data directly from Laravel controllers as Vue props.

---

## ⚙️ Inner Workings & Data Flow

### 1. Registration Flow
- **Input:** Student ID, Name, Email, Password.
- **Process:** Controller queries `ApprovedStudent`. If a match is found, a `User` and a `VoterProfile` are created within a database transaction.
- **Outcome:** The user is authenticated and redirected to the Voter Dashboard.

### 2. Voting Process (`VotingController`)
- **Validation:** Checks if the election `isActive()` and if the user has already voted (`Vote::where(...)`).
- **Submission:** Loops through selected candidates, creates `Vote` records, and increments `votes_count` on the `Candidate` model.
- **Verification:** Generates a receipt showing the user's specific selections.

### 3. Real-Time Analytics (`ResultController`)
- **Aggregation:** Calculates turnout percentages and candidate standings.
- **Frontend:** Vue.js renders progress bars and leaderboards using the aggregated data.

---

## 🚀 Database Optimization Strategy
The session focused on transitioning from a "Thin Database" (logic in PHP) to a "Thick Database" (logic in MySQL) for better performance and integrity.

### 1. Stored Procedures (`CALL`)
- **`sp_CastBallot`**: Handle the entire voting transaction inside MySQL to ensure atomicity and prevent race conditions.
- **`sp_RegisterVoter`**: Consolidate user and profile creation to prevent orphaned records.

### 2. Triggers
- **`after_vote_insert`**: Automatically update `candidates.votes_count` whenever a vote is cast, removing the need for manual increments in PHP.

### 3. Database Views
- **`view_election_standings`**: Use Window Functions (`RANK() OVER`) in MySQL to calculate rankings in real-time.
- **`view_turnout_stats`**: Pre-calculate percentages for the Admin Dashboard.

### 4. Advanced Indexing
- **Composite Index:** `votes(user_id, election_id)` to speed up "Already Voted" checks.
- **Unique Index:** `voter_profiles(student_id)` to ensure data integrity.

---

## 🛠️ Refactoring Recommendations

### What to Keep in PHP (Laravel):
*   **Auth & Security:** Keep `User` model for Laravel's session and hashing logic.
*   **File Management:** Keep photo upload/delete logic in `CandidateController`.
*   **Validation:** Use Laravel's request validation for user-friendly error messages.

### What to Move to MySQL:
*   **Batch Operations:** Moving the voting loop to a Stored Procedure.
*   **Mathematical Aggregations:** Using Views for result calculations.
*   **Data Integrity:** Enforcing strict `ON DELETE CASCADE` foreign keys.

---

**End of Session Report**  
*Generated on: May 8, 2026*
