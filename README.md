# 🗳️ ICSA Online Voting System

A secure online voting platform for the Institute of Computing Student Association (ICSA) at Davao del Norte State College.

**Built with:** Laravel 12 • Vue.js 3 • Inertia.js • Tailwind CSS • MySQL

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
git clone https://github.com/carbajosafroyd/icsa-ovs-lara-vue.git
cd icsa-ovs-lara-vue
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
npm install chart.js
```

4. **Configure `.env` file**
```env
DB_DATABASE=icsa_ovs_db
DB_USERNAME=root
DB_PASSWORD=

MAIL_HOST=smtp.gmail.com
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
```

5. **Setup database**
- Create database `icsa_ovs_db` in phpMyAdmin
```bash
php artisan migrate:fresh
php artisan migrate:fresh --seed
php artisan storage:link
```

6. **Build and run**
```bash
npm run build
```

Open **two terminals**:
```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

Visit: **http://localhost:8000**

---

## 🔑 Default Login

**Admin Account:**
- Email: `admin@icsa.com`
- Password: `password`

**Voter Account:**
- Register at `/register` using an approved student ID

---

## ✨ Features

### Three User Roles
- **Admin** - Manage elections, positions, candidates, and voters
- **Voter** - Register, vote, and view results
- **Candidate** - View profile, platform, and election progress

### Security
- One vote per student per election
- Password encryption

### Real-time
- Live vote counting
- Dynamic charts
- Instant result updates

---

## 🐛 Common Issues

**"Vite manifest not found"**
```bash
npm run build
```

**"Access denied for database"**
- Check `.env` DB credentials
- Ensure MySQL is running in XAMPP

**"Images not loading"**
```bash
php artisan storage:link
```

**Port 8000 in use**
```bash
php artisan serve --port=8080
```

**White screen**
- Check browser console (F12)
- Ensure both `php artisan serve` and `npm run dev` are running

---

## 📁 Project Structure

```
app/
├── Http/Controllers/    # Backend logic
├── Models/             # Database models
├── Mail/              # Email templates

resources/
├── js/
│   ├── pages/         # Vue pages (admin, voter, candidate)
│   ├── components/    # Reusable Vue components
│   └── layouts/       # Page layouts
├── views/            # Landing pages (Blade)
└── css/              # Styles

database/
├── migrations/       # Database schema
└── seeders/         # Sample data
```

---

## 👥 Team

**ICSA OVS Team**
- Froyd Carbajosa
- Kimbie Batilong
- Liezel Tumagan
- Felaura Vivien Golosino

*Final Project - Institute of Computing, DNSC*

---

**Thank You**
