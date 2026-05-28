# GEMINI.md - ICSA Online Voting System

## 🗳️ Project Overview
The **ICSA Online Voting System** is a secure, role-based platform designed for the Institute of Computing Student Association (ICSA) at Davao del Norte State College. It facilitates digital elections with real-time counting and comprehensive management tools.

### Core Tech Stack
- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Vue.js 3 with Inertia.js 2.0 (SSR enabled)
- **Styling:** Tailwind CSS 4.0
- **Icons:** Lucide Vue Next & Heroicons
- **Charts:** Chart.js (via `vue-chartjs`)
- **Authentication:** Laravel Fortify
- **Build Tool:** Vite with `@laravel/vite-plugin-wayfinder`

---

## 🏗️ Architecture & Structure

### Role-Based Routing
The application is strictly partitioned by user roles, each with its own prefix and middleware:
- `/admin`: Election management, candidate approval, and system oversight.
- `/voter`: Registration, voting, and receipt viewing.
- `/candidate`: Profile management, platform updates, and result tracking.

### Key Directories
- `app/Http/Controllers/`: Contains role-specific controllers (e.g., `CandidateController`, `VotersController`).
- `resources/js/pages/`: Organized by role:
    - `admin/`: Admin-only dashboards and management views.
    - `voter/`: Voting interfaces and profile settings.
    - `candidate/`: Candidate-specific profile and dashboard.
- `resources/js/components/`: Reusable UI components (e.g., `AppShell`, `Breadcrumbs`, `AlertError`).
- `database/migrations/`: Defines the election data model (elections, positions, candidates, votes, approved_students).

---

## 🛠️ Development Workflow

### Key Commands
| Task | Command |
| :--- | :--- |
| **Install Dependencies** | `composer install && npm install` |
| **Development Server** | `npm run dev` (Runs Vite + Laravel Serve via concurrently) |
| **Database Reset/Seed** | `php artisan migrate:fresh --seed` |
| **Production Build** | `npm run build` |
| **Testing** | `php artisan test` (Uses Pest) |
| **Linting/Formatting** | `npm run lint` / `npm run format` |

### Database Seeding
The project uses `AdminUserSeeder` and `ApprovedStudentsSeeder`. To get started, use:
```bash
php artisan migrate:fresh --seed
```
**Default Admin:** `admin@icsa.com` / `password`

---

## 💡 Development Conventions

### Shared Data (Inertia)
Global data is shared via `app/Http/Middleware/HandleInertiaRequests.php`. This includes:
- `auth.user`: Current authenticated user (with role and photo).
- `flash`: Success/Error notifications.
- `sidebarOpen`: Persistent sidebar state.

### Component Design
- Prefer composition-based layouts using `AppShell`.
- Use the shared `components/` directory for UI elements to maintain consistency.
- Standardized error handling via the `AlertError` component.

### Data Modeling
- **Elections:** Can be activated/deactivated.
- **Voters:** Must match an entry in `approved_students` to register.
- **Votes:** Enforces one vote per student per election.

---

## 🚀 Deployment Notes
- Ensure `APP_KEY` is generated.
- Run `php artisan storage:link` to handle profile and candidate photos.
- `npm run build` is mandatory for production to generate the Vite manifest.
