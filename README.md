# 🗳️ ICSA Online Voting System

A secure, high-performance online voting platform for the Institute of Computing Student Association (ICSA) at Davao del Norte State College.

**Built with:** Laravel 12 • Vue.js 3 • Inertia.js 2.0 • Tailwind CSS 4.0 • MySQL (Database-First Architecture)

---

## 🚀 Quick Start

### Prerequisites
- XAMPP 8.2+ with PHP & MySQL
- Node.js 18+
- Composer 2+

### Installation

1. **Clone the repository**
```bash
cd C:\xampp\htdocs
git clone https://github.com/carbajosafroyd/icsa-ovs-lara-vue.git icsa-online-voting-system
cd icsa-online-voting-system
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Setup environment**
```bash
copy .env.example .env
php artisan key:generate
```

4. **Configure `.env` file**
```env
DB_DATABASE=icsa_ovs_db
DB_USERNAME=root
DB_PASSWORD=

# Required for correct time display
APP_TIMEZONE=Asia/Manila

# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls

# Queue Configuration (Database driver)
QUEUE_CONNECTION=database
```

5. **Setup Database & Storage**
- Create database `icsa_ovs_db` in phpMyAdmin
- Run migrations and seed default accounts:
```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

6. **Build and Run**
```bash
npm run build
```

Open **three terminals**:
```bash
# Terminal 1: Web Server
php artisan serve

# Terminal 2: Vite (Development)
npm run dev

# Terminal 3: Email Queue Worker (CRITICAL for sending credentials)
php artisan queue:work
```

Visit: **http://localhost:8000**

---

## 🔑 Default Login

**Admin Account:**
- Email: `admin@icsa.com`
- Password: `password`

**Voter Account:**
- Register at `/register` using an approved student ID (check `approved_students` table)

---

## ✨ Features & Architecture

### 🛡️ Database-First Architecture
This system utilizes a professional **Database-First** approach to ensure data integrity and maximum performance:
- **Stored Procedures:** 9 specialized procedures handle atomic operations (e.g., `sp_CastBallot`, `sp_CreateCandidate`, `sp_RegisterVoter`).
- **Database Views:** 9 optimized views for real-time reporting, dashboards, and complex joins (e.g., `view_election_results`, `view_election_statistics`).
- **Triggers:** Automatic vote count updates via `trg_UpdateVoteCount`.

### 📧 Advanced Features
- **Role-Based Access Control (RBAC):** Distinct interfaces for Admin, Voter, and Candidate roles.
- **Background Email Queuing:** Candidate credentials and notifications are sent via Laravel Queues to prevent UI freezing.
- **Timezone Sync:** Full synchronization between Laravel and MySQL for accurate "time ago" reporting (Asia/Manila).
- **Responsive Charts:** Real-time election result visualization using Chart.js.

---

## 🐛 Common Issues

**"Vite manifest not found"**
- Run `npm run build` or ensure `npm run dev` is running.

**"Table 'cache' doesn't exist"**
- Ensure you have run `php artisan migrate`.

**"Emails not sending"**
- Ensure your `.env` credentials are correct and `php artisan queue:work` is running.

**"Time is 8 hours ahead/behind"**
- Ensure `APP_TIMEZONE=Asia/Manila` is set in `.env` and `timezone` is set to `+08:00` in `config/database.php`.

---

## 📁 Project Structure

```
app/
├── Http/Controllers/    # Backend logic (calls SPs and Views)
├── Mail/              # Queuable Email classes
├── Middleware/         # Role-based protection

resources/
├── js/
│   ├── pages/         # Vue interfaces (admin/, voter/, candidate/)
│   ├── components/    # Reusable UI components
│   └── layouts/       # Shared layouts (AppShell, VoterLayout)
├── views/            # Root Blade template (app.blade.php)

database/
├── migrations/       # Standard schema
├── seeders/         # Whitelist and admin seeds
└── icsa_ovs_db.sql   # Full optimized SQL dump (Procedures/Views)
```

---

## 👥 Team

**ICSA OVS Team**
- Froyd Carbajosa-(FORMER LEADER)
- Kimbie Batilong
- Liezel Tumagan
- Felaura Vivien Golosino
- Monch Walter P Quines (NEW MEMBER)
- Bryl James Pagalan (NEW MEMBER)

*Final Project - Institute of Computing, DNSC*

---

**Thank You**
