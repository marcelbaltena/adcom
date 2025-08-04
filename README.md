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
│   │   │   ├── Auth/                    # Breeze authentication
│   │   │   ├── CompanyController.php
│   │   │   ├── CustomerController.php
│   │   │   ├── ProjectController.php
│   │   │   ├── MilestoneController.php
│   │   │   ├── TaskController.php
│   │   │   └── TimeEntryController.php  # (to be implemented)
│   │   └── Middleware/
│   ├── Models/
│   │   ├── Assignee.php
│   │   ├── Attachment.php
│   │   ├── BudgetAllocation.php
│   │   ├── Client.php
│   │   ├── Comment.php
│   │   ├── Company.php
│   │   ├── Mention.php
│   │   ├── Milestone.php
│   │   ├── Project.php
│   │   ├── ProjectMilestone.php
│   │   ├── ProjectTask.php
│   │   ├── ProjectTeam.php
│   │   ├── ProjectTemplate.php
│   │   ├── RolePermission.php
│   │   ├── Subtask.php
│   │   ├── SubtaskTemplate.php
│   │   ├── Task.php
│   │   ├── TimeEntry.php (polymorphic)
│   │   ├── User.php
│   │   └── UserWorkSchedule.php
│   └── Livewire/                        # Livewire components
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── admin/                       # Admin panel views
│   │   ├── auth/                        # Breeze auth views
│   │   ├── companies/                   # Company management
│   │   ├── components/                  # Blade components
│   │   ├── customers/                   # Customer CRUD
│   │   ├── dashboard/                   # Dashboard views
│   │   ├── layouts/                     # Layout templates
│   │   ├── livewire/                    # Livewire views
│   │   ├── profile/                     # User profile
│   │   ├── project-templates/           # Project template management
│   │   ├── projects/                    # Project views
│   │   ├── service-templates/           # Service templates
│   │   ├── time-tracking/               # Time tracking (to be created)
│   │   ├── dashboard.blade.php          # Main dashboard
│   │   └── welcome.blade.php            # Landing page
│   ├── css/
│   │   └── app.css                      # Tailwind imports
│   └── js/
│       ├── app.js                       # Alpine.js & main JS
│       └── components/                  # JS components
├── routes/
│   ├── web.php                          # Web routes
│   ├── auth.php                         # Breeze auth routes
│   └── api.php                          # API routes (planned)
├── public/
│   ├── build/                           # Compiled assets
│   └── storage/                         # Public file storage
└── storage/
    ├── app/                             # Application files
    ├── framework/                       # Cache & sessions
    └── logs/                            # Application logs
```

## 🎨 View Structure Details

### Implemented Views
- **admin/** - Custom admin panel views
- **auth/** - Login, register, password reset (Laravel Breeze)
- **companies/** - Multi-company management interface
- **customers/** - Customer relationship management
- **dashboard/** - Dashboard components and widgets
- **project-templates/** - Reusable project templates
- **projects/** - Project management interface
- **service-templates/** - Service template library

### To Be Implemented
- **time-tracking/** - Time entry and tracking views
- **invoices/** - Invoice generation and management
- **reports/** - Analytics and reporting dashboards
- **milestones/** - Milestone specific views
- **tasks/** - Task management views

## 🗄️ Database Schema

### Core Tables (36 total)

#### Company & User Management
- **companies** - Multi-tenant bedrijven
- **users** - Gebruikers met authenticatie
- **customers** - Klanten per bedrijf
- **clients** - Legacy client tabel
- **role_permissions** - Rol-gebaseerde toegangscontrole (21 entries)
- **user_work_schedules** - Werkschema's per gebruiker
- **user_monthly_hours** - Maandelijkse uren tracking

#### Project Structure
- **projects** - Hoofdprojecten (7 active)
- **milestones** - Project fases (15 entries)
- **tasks** - Taken binnen milestones (8 entries)
- **subtasks** - Subtaken (5 entries)
- **project_teams** - Team toewijzingen
- **project_milestones** - Milestone koppelingen
- **project_tasks** - Task koppelingen
- **project_subtasks** - Subtask koppelingen

#### Templates System
- **project_templates** - Herbruikbare project templates
- **service_templates** - Service catalogus (3 templates)
- **milestone_templates** - Milestone templates (4 entries)
- **task_templates** - Task templates (5 entries)
- **subtask_templates** - Subtask templates (3 entries)

#### Time & Budget Tracking
- **time_entries** - Polymorphic tijd registratie (ready to use)
- **budget_allocations** - Budget verdelingen

#### Collaboration Features
- **assignees** - Polymorphic toewijzingen (5 entries)
- **watchers** - Volgers systeem (5 entries)
- **comments** - Polymorphic comments
- **attachments** - File uploads
- **mentions** - @ mentions in comments
- **activity_logs** - Audit trail (12 entries)

#### System Tables
- **migrations** - Database versioning (71 migrations)
- **sessions** - Active user sessions (189 active)
- **cache** - Application cache
- **cache_locks** - Cache locking
- **jobs** - Queue jobs
- **job_batches** - Batch processing
- **failed_jobs** - Failed queue jobs
- **password_reset_tokens** - Password reset flows

### Database Relations
```
Companies
    ↓
Customers/Clients
    ↓
Projects → Project Templates
    ↓        ↓
Milestones → Milestone Templates
    ↓          ↓
Tasks ------→ Task Templates
    ↓            ↓
Subtasks ---→ Subtask Templates

Time Entries (polymorphic - can attach to any level)
Assignees (polymorphic - can assign users to any entity)
Watchers (polymorphic - follow any entity)
Comments (polymorphic - comment on any entity)
Attachments (polymorphic - attach files to any entity)
```

### Key Features
- **Polymorphic Relations** - Voor flexibele koppelingen (time entries, comments, etc.)
- **Template System** - Complete template hiërarchie voor hergebruik
- **Activity Tracking** - Volledige audit trail
- **Team Collaboration** - Assignees, watchers, mentions
- **Multi-tenant** - Company-based isolation

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

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

## 🔧 Maintenance

### Clear Caches
```bash
php artisan optimize:clear
# Or individually:
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Database Maintenance
```bash
# Backup database
php artisan backup:run

# Optimize tables
php artisan db:optimize
```

## 📈 Performance Tips

1. **Enable OPcache** for production
2. **Use Redis** for sessions and cache
3. **Enable route caching**: `php artisan route:cache`
4. **Enable config caching**: `php artisan config:cache`
5. **Use queues** for heavy operations

## 🐛 Known Issues

- Time tracking UI needs completion
- Invoice module not yet implemented
- Some translations missing
- Mobile responsiveness needs work

## 🗺️ Roadmap

### Q1 2025
- [ ] Complete time tracking
- [ ] Basic invoicing
- [ ] Email notifications

### Q2 2025
- [ ] Advanced reporting
- [ ] Client portal
- [ ] Mobile app

### Q3 2025
- [ ] API v1
- [ ] Integrations (Slack, etc)
- [ ] Advanced permissions

## 📞 Support

Voor vragen of problemen:
- Create een issue op GitHub
- Contact: [je email of contact info]

## ⚖️ License

This is proprietary software. All rights reserved.

---

Built with ❤️ using Laravel and Tailwind CSS
