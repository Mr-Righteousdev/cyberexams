# Architecture

**Analysis Date:** 2026-04-23

## Pattern Overview

**Overall:** Laravel MVC with Livewire and Fortify

This application follows the standard Laravel MVC (Model-View-Controller) pattern enhanced with Livewire for reactive frontend components and Laravel Fortify for authentication. The architecture leverages Laravel 13's application skeleton with the new bootstrap configuration approach.

**Key Characteristics:**
- **Laravel 13** - Uses the new `Application::configure()` pattern in `bootstrap/app.php`
- **Livewire 4** - Full component-based reactivity for dynamic UI without custom JavaScript
- **Flux UI** - Component library for building Livewire interfaces with Tailwind CSS 4
- **Fortify 1** - Headless authentication backend handling all auth routes and controllers
- **Eloquent ORM** - Database abstraction with model层的 relationships
- **Service Providers** - Modular service registration via providers

## Layers

**Application Layer:**
- Purpose: Entry point and HTTP request handling
- Location: `app/Http/Controllers/` and Livewire components
- Contains: Base Controller, Livewire components for settings
- Depends on: Models, Fortify actions
- Used by: Routes in `routes/web.php` and `routes/settings.php`

**Model/Data Layer:**
- Purpose: Data representation and database interaction
- Location: `app/Models/`
- Contains: `User.php` model with authentication and TwoFactorAuthenticatable traits
- Depends on: Database migrations, factories
- Used by: Controllers, Livewire components, Fortify actions

**View/Presentation Layer:**
- Purpose: Rendering HTML and handling UI state
- Location: `resources/views/`
- Contains: Blade templates, layouts, Livewire auth views, Flux components
- Depends on: Livewire components, Flux
- Used by: Routes, Livewire component rendering

**Authentication Layer:**
- Purpose: User authentication and authorization
- Location: `app/Providers/FortifyServiceProvider.php`, `app/Actions/Fortify/`
- Contains: Custom Fortify actions (CreateNewUser, ResetUserPassword)
- Depends on: Laravel Fortify package, User model
- Used by: Fortify routes and views

**Service Layer:**
- Purpose: Application configuration and bootstrapping
- Location: `app/Providers/AppServiceProvider.php`
- Contains: Date formatting, DB command protection, password rules
- Depends on: Carbon, Database, Validation
- Used by: Application bootstrap

## Request Lifecycle

**Public Request (Home/Welcome):**
1. Request hits `public/index.php` → loads Laravel bootstrap
2. Router matches route in `routes/web.php` → `Route::view('/', 'welcome')`
3. Blade template `resources/views/welcome.blade.php` rendered
4. Response returned

**Authenticated Request (Dashboard):**
1. Request hits through HTTP kernel
2. Route matched in `routes/web.php` with `auth` middleware group
3. `auth` middleware checks authentication
4. `verified` middleware checks email verification
5. Blade template `resources/views/dashboard.blade.php` rendered

**Livewire Settings Request:**
1. Request hits route in `routes/settings.php` → `Route::livewire('settings/profile', Profile::class)`
2. Middleware stack runs (auth, password.confirm for security)
3. Livewire component `App\Livewire\Settings\Profile` instantiated
4. Component mounts with current user data
5. User interactions trigger component actions
6. Component re-renders and updates UI via Flux

**Authentication Flow:**
1. Request to `/login` triggers Fortify login view
2. Form posts to Fortify's internal controller
3. Fortify validates credentials via `LoginController`
4. Session established, redirect to dashboard
5. Two-factor challenge if enabled

## Data Flow

**User Registration:**
1. User submits registration form at `/register`
2. Fortify `CreatesNewUsers` contract invokes `CreateNewUser` action
3. `CreateNewUser::create()` validates input via `ProfileValidationRules` and `PasswordValidationRules`
4. New `User` record created in database
5. User logged in automatically, redirected to email verification

**Profile Update:**
1. User navigates to `/settings/profile`
2. `Profile` Livewire component mounts with user data
3. User submits profile update form
4. `updateProfileInformation()` action validates via `ProfileValidationRules`
5. User record updated, email verification reset if email changed
6. Success toast displayed via `Flux::toast()`

## Service Provider Registration

**Provider Chain:**
```
bootstrap/app.php
    ↓
$app->register(AppServiceProvider::class)
    ↓
$app->register(FortifyServiceProvider::class)
```

**Registration in `bootstrap/providers.php`:**
```php
return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
];
```

**AppServiceProvider:**
- Registers: Date formatting (CarbonImmutable), DB command protection, Password validation rules
- Loaded: Automatically by Laravel bootstrap

**FortifyServiceProvider:**
- Registers: Fortify actions, views, rate limiting
- Loaded: Automatically after AppServiceProvider
- Configures: Login, registration, password reset, 2FA views

## Route Organization

**Routes File Structure:**
| File | Purpose | Middleware |
|------|---------|------------|
| `routes/web.php` | Web routes | Varies per route |
| `routes/settings.php` | Settings routes | auth, verified |
| `routes/console.php` | Artisan commands | None |
| `routes/channels.php` | WebSocket channels | None |

**Route Groups in `routes/web.php`:**
```php
// Public routes
Route::view('/', 'welcome')->name('home');

// Authenticated routes (auth + verified)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});
```

**Route Groups in `routes/settings.php`:**
```php
// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');
    Route::livewire('settings/profile', Profile::class)->name('profile.edit');
});

// Verified routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('settings/appearance', Appearance::class);
    Route::livewire('settings/security', Security::class);
});
```

## Key Abstractions

**Livewire Components:**
- `App\Livewire\Settings\Profile` - Profile editing
- `App\Livewire\Settings\Appearance` - Appearance settings
- `App\Livewire\Settings\Security` - Security and 2FA
- `App\Livewire\Settings\TwoFactor\RecoveryCodes` - 2FA recovery codes
- `App\Livewire\Settings\DeleteUserForm` - Account deletion
- `App\Livewire\Actions\Logout` - Logout action

**Fortify Actions:**
- `App\Actions\Fortify\CreateNewUser` - User registration
- `App\Actions\Fortify\ResetUserPassword` - Password reset

**Validation Concerns:**
- `App\Concerns\ProfileValidationRules` - Profile field validation
- `App\Concerns\PasswordValidationRules` - Password strength rules

## Error Handling

**Strategy:** Laravel's default exception handling via `bootstrap/app.php` configuration

**Patterns:**
- HTTP exceptions rendered as error pages
- Validation errors returned to Livewire components
- Authentication exceptions redirect to login
- Rate limiting returns 429 response

## Cross-Cutting Concerns

**Logging:** Laravel default logging via `config/logging.php`

**Validation:** Form request validation and inline validation in Livewire actions

**Authentication:** Handled by Fortify with TwoFactorAuthenticatable on User model

---

*Architecture analysis: 2026-04-23*