# AdCom - Agency Communication Management Platform

> ⚠️ **Work in Progress** - Deze applicatie is momenteel in actieve ontwikkeling. Niet alle features zijn compleet of stabiel.

Een moderne web applicatie voor communicatiebureaus om klanten, projecten, taken en tijd efficiënt te beheren.

## 🚧 Development Status

Dit project is momenteel in **ALPHA** fase. Verwacht:
- 🐛 Bugs en onverwacht gedrag
- 🔧 Frequente breaking changes
- 📝 Incomplete documentatie
- ⚡ Dagelijkse updates

### Development Progress
- [x] Project setup & architectuur
- [x] Database structuur
- [x] Basis models & relaties
- [x] User authenticatie (Laravel Breeze)
- [x] Company & Customer management
- [x] Project templates
- [ ] Time tracking implementatie (in progress)
- [ ] Facturatie module
- [ ] Reporting & analytics
- [ ] Client portal
- [ ] API endpoints

## 🚀 Features

### ✅ Implemented
- **Multi-Company Support** - Beheer meerdere bedrijven binnen één platform
- **Customer Management** - Volledig CRM systeem voor klantbeheer
- **Project Templates** - Herbruikbare project sjablonen voor snelle setup
- **User Management** - Rollen, permissies en team beheer
- **Project Structure** - Hiërarchische structuur met Projects → Milestones → Tasks → Subtasks
- **Budget Tracking** - Real-time budget monitoring per project/milestone

### 🚧 In Development
- **Time Tracking** - Polymorphic tijd registratie op elk niveau (project/milestone/task/subtask)
- **Reporting** - Uitgebreide rapporten en dashboards
- **Invoice Generation** - Automatische facturatie op basis van gewerkte uren

### 📋 Planned
- **Email Integration** - Directe email communicatie vanuit projecten
- **File Management** - Centrale opslag voor project bestanden
- **Client Portal** - Klanten toegang geven tot hun projecten
- **Mobile App** - iOS/Android apps voor tijd registratie

## 🛠️ Tech Stack

- **Backend**: Laravel 12.x (Latest)
- **Frontend**: Blade Templates + Livewire 3
- **Database**: MySQL 8.0
- **Styling**: Tailwind CSS 3.4
- **JavaScript**: Alpine.js 3.x
- **Server**: Linux (Ubuntu) + Nginx
- **PHP**: 8.2+

## 📦 Dependencies

### Composer Packages
```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0",
        "laravel/tinker": "^2.10.1",
        "livewire/livewire": "^3.6"
    },
    "require-dev": {
        "fakerphp/faker": "^1.23",
        "laravel/breeze": "^2.3",
        "laravel/pail": "^1.2.2",
        "laravel/pint": "^1.13",
        "laravel/sail": "^1.41",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.6",
        "phpunit/phpunit": "^11.5.3"
    }
}
```

### NPM Packages
```json
{
    "devDependencies": {
        "@tailwindcss/forms": "^0.5.2",
        "alpinejs": "^3.4.2",
        "autoprefixer": "^10.4.21",
        "axios": "^1.8.2",
        "tailwindcss": "^3.4.17",
        "vite": "^6.2.4",
        "laravel-vite-plugin": "^1.2.0"
    }
}
```

### Key Package Descriptions

#### Core Packages
- **laravel/framework v12** - Cutting-edge Laravel versie met alle nieuwe features
- **livewire/livewire** - Full-stack framework voor dynamische interfaces zonder veel JavaScript
- **laravel/breeze** - Authenticatie scaffolding met Tailwind CSS

#### Development Tools
- **laravel/pail** - Real-time log viewer in je terminal
- **laravel/sail** - Docker development environment
- **laravel/pint** - Code style fixer (opinionated PHP code style)

### Upcoming Packages (Planned)
- **spatie/laravel-permission** - Voor geavanceerd role management
- **barryvdh/laravel-dompdf** - PDF generatie voor facturen
- **maatwebsite/excel** - Excel export voor rapportages
- **laravel/sanctum** - API authenticatie
- **spatie/laravel-backup** - Automated backups

## 📁 Project Structure

```
adcom/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   └── Models/
│       ├── Client.php
│       ├── Company.php
│       ├── Project.php
│       ├── Milestone.php
│       ├── Task.php
│       ├── Subtask.php
│       ├── TimeEntry.php (polymorphic)
│       ├── User.php
│       └── ...
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
│       ├── projects/
│       ├── time-tracking/
│       └── ...
├── routes/
│   ├── web.php
│   └── api.php
└── ...
```

## 🗄️ Database Schema

### Core Relations
```
Companies
    ↓
Customers/Clients
    ↓
Projects
    ↓
Milestones (optional)
    ↓
Tasks
    ↓
Subtasks (optional)

Time Entries (polymorphic - can attach to any level)
```

### Key Models
- **Company**: Multi-tenant ondersteuning
- **Customer**: Klanten per bedrijf
- **Project**: Hoofdprojecten met budget en timeline
- **Milestone**: Project fases (design, development, etc.)
- **Task**: Concrete taken binnen milestones
- **TimeEntry**: Polymorphic tijd registratie
- **User**: Gebruikers met rollen en permissies

## 🚀 Installation

> ⚠️ **Note**: Deze instructies zijn voor development doeleinden. Production deployment vereist aanvullende configuratie.

### Requirements
- PHP 8.2+ (8.3 recommended voor Laravel 12)
- MySQL 8.0+
- Composer 2.x
- Node.js 18+ & NPM
- Redis (optional, voor caching)

### Setup Steps

1. **Clone the repository**
```bash
git clone https://github.com/marcelbaltena/adcom.git
cd adcom
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install Node dependencies**
```bash
npm install
```

4. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure database**
Update `.env` with your database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=adcom
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. **Run migrations**
```bash
php artisan migrate
```

7. **Build assets**
```bash
npm run build
# Voor development met hot reload:
npm run dev
```

8. **Start development server**
```bash
# Optie 1: Gebruik de ingebouwde dev command (start alles tegelijk)
composer dev

# Optie 2: Start services individueel
php artisan serve
php artisan queue:listen  # In nieuwe terminal
npm run dev               # In nieuwe terminal
```

9. **Access the application**
- Application: http://localhost:8000
- Login with the user you created during setupelbaltena/adcom.git
cd adcom
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure database**
Update `.env` with your database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=adcom
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **Configure additional services** (optional)
```
# Mail settings
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@adcom.app"

# Storage
FILESYSTEM_DISK=local

# Queue driver
QUEUE_CONNECTION=database

# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis

# Time tracking settings
DEFAULT_HOURLY_RATE=75.00
TIME_TRACKING_APPROVAL_REQUIRED=true
```

5. **Run migrations**
```bash
php artisan migrate
```

6. **Build assets**
```bash
npm run build
```

7. **Start development server**
```bash
php artisan serve
```

## 🔧 Development

### Branch Structure
- `master` - Production-ready code
- `feature/time-tracking` - Time tracking implementation
- `feature/invoicing` - Invoice generation (planned)
- `feature/reporting` - Analytics & reporting (planned)

### Making Changes
```bash
# Create new feature branch
git checkout -b feature/your-feature

# Make changes and commit
git add .
git commit -m "Description of changes"

# Push to GitHub
git push origin feature/your-feature
```

## 📊 Time Tracking Feature

Het time tracking systeem gebruikt polymorphic relations om tijd te kunnen registreren op:
- **Project niveau** - Voor algemene project werkzaamheden
- **Milestone niveau** - Voor fase-specifiek werk
- **Task niveau** - Voor concrete taken
- **Subtask niveau** - Voor gedetailleerde sub-taken

### API Endpoints
```
POST   /api/time-entries/start     - Start timer
POST   /api/time-entries/stop      - Stop active timer
POST   /api/time-entries           - Create manual entry
GET    /api/time-entries           - List entries
PUT    /api/time-entries/{id}     - Update entry
DELETE /api/time-entries/{id}     - Delete entry
```

## 🤝 Contributing

Dit is momenteel een privé project. Voor vragen of suggesties, neem contact op met de ontwikkelaar.

## 📝 License

Proprietary - All rights reserved

## 👤 Contact

**Marcel Baltena**  
GitHub: [@marcelbaltena](https://github.com/marcelbaltena)

---

*Laatste update: Januari 2025*