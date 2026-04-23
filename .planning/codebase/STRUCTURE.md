# Codebase Structure

**Analysis Date:** 2026-04-23

## Directory Layout

```
examshield/
├── app/                        # Application code (PSR-4 autoload)
│   ├── Actions/                # Action classes
│   ├── Concerns/              # Shared concerns/traits
│   ├── Http/                  # HTTP layer
│   ├── Livewire/              # Livewire components
│   ├── Models/                 # Eloquent models
│   └── Providers/              # Service providers
├── bootstrap/                  # Laravel bootstrap
│   ├── app.php                # Application configuration
│   ├── cache/                 # Compiled cache
│   └── providers.php          # Provider manifest
├── config/                     # Configuration files
├── database/                   # Database layer
│   ├── factories/             # Eloquent factories
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── public/                     # Web root (document root)
├── resources/                  # Frontend resources
│   ├── css/                  # Tailwind CSS
│   ├── js/                   # JavaScript
│   └── views/                # Blade templates
├── routes/                     # Route definitions
├── storage/                   # Storage (logs, cache, etc.)
├── tests/                     # Test suite
├── vendor/                     # Composer dependencies
├── artisan                    # Artisan CLI entry
├── composer.json              # PHP dependencies
├── package.json              # Node dependencies
├── phpunit.xml               # PHPUnit config
├── vite.config.js            # Vite build config
└── pint.json                 # Pint formatter config
```

## Directory Purposes

**app/Actions/ - Action Classes:**
- Purpose: Business logic for authentication workflows
- Contains: `Fortify/CreateNewUser.php`, `Fortify/ResetUserPassword.php`
- Key files:
  - `app/Actions/Fortify/CreateNewUser.php` - User registration validation and creation
  - `app/Actions/Fortify/ResetUserPassword.php` - Password reset action

**app/Concerns/ - Shared Concerns:**
- Purpose: Reusable validation rules and traits
- Contains: `ProfileValidationRules.php`, `PasswordValidationRules.php`
- Key files:
  - `app/Concerns/ProfileValidationRules.php` - Name/email validation rules
  - `app/Concerns/PasswordValidationRules.php` - Password strength rules

**app/Http/Controllers/ - Controllers:**
- Purpose: Base HTTP controller
- Contains: `Controller.php` (abstract base class)
- Key files:
  - `app/Http/Controllers/Controller.php` - Abstract base for all controllers

**app/Livewire/ - Livewire Components:**
- Purpose: Interactive UI components
- Contains: Settings components, auth actions
- Key files:
  - `app/Livewire/Settings/Profile.php` - Profile settings component
  - `app/Livewire/Settings/Appearance.php` - Appearance settings
  - `app/Livewire/Settings/Security.php` - Security/2FA settings
  - `app/Livewire/Settings/TwoFactor/RecoveryCodes.php` - 2FA recovery codes
  - `app/Livewire/Settings/DeleteUserForm.php` - Account deletion
  - `app/Livewire/Actions/Logout.php` - Logout action

**app/Models/ - Eloquent Models:**
- Purpose: Database entity representation
- Contains: `User.php` model
- Key files:
  - `app/Models/User.php` - User model with authentication and 2FA support

**app/Providers/ - Service Providers:**
- Purpose: Application service configuration
- Contains: `AppServiceProvider.php`, `FortifyServiceProvider.php`
- Key files:
  - `app/Providers/AppServiceProvider.php` - Core app configuration
  - `app/Providers/FortifyServiceProvider.php` - Fortify setup

**bootstrap/ - Application Bootstrap:**
- Purpose: Laravel initialization and configuration
- Contains: Application config, provider manifest
- Key files:
  - `bootstrap/app.php` - Application configuration (Laravel 13 pattern)
  - `bootstrap/providers.php` - Provider list

**config/ - Configuration Files:**
- Purpose: Application settings
- Contains: Environment configuration
- Key files:
  - `config/app.php` - Application settings
  - `config/fortify.php` - Fortify configuration
  - `config/database.php` - Database configuration
  - `config/auth.php` - Authentication guards
  - `config/cache.php` - Cache configuration
  - `config/session.php` - Session configuration
  - `config/logging.php` - Logging configuration
  - `config/mail.php` - Mail configuration
  - `config/queue.php` - Queue configuration
  - `config/filesystems.php` - Filesystem configuration
  - `config/services.php` - Third-party services

**database/migrations/ - Database Migrations:**
- Purpose: Database schema definition
- Contains: Schema building migrations
- Key files:
  - `database/migrations/0001_01_01_000000_create_users_table.php` - Users, sessions, password reset
  - `database/migrations/2025_08_14_170933_add_two_factor_columns_to_users_table.php` - 2FA columns
  - `database/migrations/0001_01_01_000001_create_cache_table.php` - Cache table
  - `database/migrations/0001_01_01_000002_create_jobs_table.php` - Jobs table

**database/factories/ - Eloquent Factories:**
- Purpose: Test data generation
- Contains: `UserFactory.php`
- Key files:
  - `database/factories/UserFactory.php` - User factory for testing

**database/seeders/ - Database Seeders:**
- Purpose: Initial data seeding
- Contains: `DatabaseSeeder.php`
- Key files:
  - `database/seeders/DatabaseSeeder.php` - Main database seeder

**public/ - Web Root:**
- Purpose: Document root for web server
- Contains: `index.php` (web entry point)
- Key files:
  - `public/index.php` - HTTP request entry point

**resources/views/ - Blade Templates:**
- Purpose: View rendering
- Contains: Layouts, components, Livewire views
- Key files:
  - `resources/views/layouts/app.blade.php` - Main app layout
  - `resources/views/layouts/auth.blade.php` - Auth layout base
  - `resources/views/layouts/auth/*.blade.php` - Auth layout variants
  - `resources/views/livewire/auth/*.blade.php` - Auth Livewire views
  - `resources/views/livewire/settings/*.blade.php` - Settings Livewire views
  - `resources/views/components/*.blade.php` - Reusable components
  - `resources/views/dashboard.blade.php` - Dashboard view
  - `resources/views/welcome.blade.php` - Welcome/public view

**resources/css/ - Stylesheets:**
- Purpose: Tailwind CSS styles
- Contains: `app.css` (Tailwind 4 entry)
- Key files:
  - `resources/css/app.css` - Tailwind CSS entry with @theme

**resources/js/ - JavaScript:**
- Purpose: Client-side JavaScript
- Contains: `app.js` (Livewire/Vite entry)
- Key files:
  - `resources/js/app.js` - JavaScript entry point

**routes/ - Route Definitions:**
- Purpose: Route registration
- Contains: Route files
- Key files:
  - `routes/web.php` - Web routes (main routes)
  - `routes/settings.php` - Settings routes (Livewire)
  - `routes/console.php` - Artisan commands

**tests/ - Test Suite:**
- Purpose: Automated testing
- Contains: Feature and unit tests
- Key files:
  - `tests/Pest.php` - Pest configuration
  - `tests/TestCase.php` - Base test case
  - `tests/Feature/Auth/*.php` - Auth tests
  - `tests/Feature/Settings/*.php` - Settings tests

## Key File Locations

**Entry Points:**
- `public/index.php` - HTTP entry point for web requests
- `artisan` - Artisan CLI entry point
- `bootstrap/app.php` - Application bootstrap configuration

**Configuration:**
- `config/app.php` - Application name, timezone, locale
- `config/fortify.php` - Fortify features
- `config/database.php` - Database connection (SQLite by default)
- `config/auth.php` - Auth guard configuration
- `.env` - Environment variables (NOT committed)

**Routes:**
- `routes/web.php` - Browser routes (home, dashboard)
- `routes/settings.php` - Settings Livewire routes

**Models:**
- `app/Models/User.php` - User model with authentication

**Service Providers:**
- `app/Providers/AppServiceProvider.php` - App configuration
- `app/Providers/FortifyServiceProvider.php` - Fortify setup

**Livewire Components:**
- `app/Livewire/Settings/Profile.php` - Profile settings
- `app/Livewire/Settings/Security.php` - Security settings
- `app/Livewire/Settings/Appearance.php` - Appearance settings

## Naming Conventions

**Files:**
- PascalCase for PHP classes: `Profile.php`, `CreateNewUser.php`
- kebab-case for Blade templates: `profile.blade.php`
- kebab-case for CSS/JS files: `app.css`, `app.js`

**Directories:**
- PascalCase for namespaces: `Actions/Fortify/`, `Livewire/Settings/`
- kebab-case for resource directories: `resources/views/`, `resources/css/`

**Routes:**
- kebab-case for route names: `profile.edit`, `security.edit`
- kebab-case for URLs: `settings/profile`, `settings/security`

## Where to Add New Code

**New Feature:**
- Primary code: `app/Livewire/` or `app/Actions/`
- Blade views: `resources/views/livewire/`
- Routes: `routes/web.php` or create new route file in `routes/`
- Tests: `tests/Feature/` or `tests/Unit/`

**New Component/Module:**
- Implementation: `app/Livewire/{Module}/`
- Views: `resources/views/livewire/{module}/`
- Tests: `tests/Feature/{Module}/`

**New Database Table:**
- Migration: `database/migrations/`
- Model: `app/Models/`
- Factory: `database/factories/`
- Seeder: `database/seeders/`

**New Configuration:**
- Config file: `config/` directory
- Registered in: Service provider `register()` method

**Utilities:**
- Shared helpers: `app/Concerns/` or create in `app/` as new class
- Validation rules: `app/Concerns/` as trait/classmethods

## Special Directories

**bootstrap/cache/:**
- Purpose: Compiled service container cache
- Generated: Yes (by Artisan)
- Committed: Partial (`.gitignore` excludes cache files)

**storage/:**
- Purpose: Logs, cached views, sessions, etc.
- Generated: Yes (by Laravel)
- Committed: Partial (framework/cache is gitignored)

**vendor/:**
- Purpose: Composer dependencies
- Generated: Yes (by Composer install)
- Committed: Yes (for consistent installs)

**node_modules/:**
- Purpose: npm dependencies
- Generated: Yes (by npm install)
- Committed: Yes (for consistent installs)

---

*Structure analysis: 2026-04-23*