# Codebase Concerns

**Analysis Date:** 2026-04-23

## Tech Debt

**Starter Kit Minimalism:**
- Issue: This is a Laravel starter kit focused on authentication. It lacks business logic, making it a skeleton application rather than a production-ready system.
- Files: Entire codebase (`app/` directory)
- Impact: Requires significant development to become a functional application
- Fix approach: This is by design for a starter kit, not a bug

**No Custom User Model Fields:**
- Issue: User model only contains default Fortify fields (name, email, password, 2FA). No additional profile fields or application-specific data.
- Files: `app/Models/User.php`, `database/migrations/0001_01_01_000000_create_users_table.php`
- Impact: Cannot store additional user data without migrations
- Fix approach: Create migrations to add required user fields

**Database Driver:**
- Issue: Default database connection is SQLite (`DB_CONNECTION=sqlite`), which is not suitable for production scalability.
- Files: `config/database.php`
- Impact: SQLite has file-level locking and limited concurrent write support
- Fix approach: Switch to MySQL/PostgreSQL for production via `DB_CONNECTION` environment variable

---

## Security Considerations

**Session Encryption Disabled:**
- Risk: Session data is stored in plain text when using database or file drivers.
- Files: `config/session.php`
- Current mitigation: `SESSION_ENCRYPT=false` by default
- Recommendations: Enable `SESSION_ENCRYPT=true` in production environments

**Debug Mode in Production:**
- Risk: Setting `APP_DEBUG=true` in production reveals detailed error messages and stack traces.
- Files: `config/app.php`
- Current mitigation: Defaults to `false`, must be explicitly enabled
- Recommendations: Ensure `APP_DEBUG=false` in production `.env`

**Password Hashing Cost:**
- Risk: Default `BCRYPT_ROUNDS=12` is secure but computationally expensive.
- Files: `.env.example`
- Current mitigation: Configurable via `BCRYPT_ROUNDS`
- Recommendations: For high-security applications, consider higher values; balance with login attempt rate limiting

**Session Serialization:**
- Risk: Using PHP session serialization (`session.serialize = 'php'`) can be vulnerable to gadget chain attacks if `APP_KEY` is leaked.
- Files: `config/session.php`
- Current mitigation: Defaults to `'json'` serialization, which is safer
- Recommendations: Keep serialization as `'json'` (already default)

**Cache Serializable Classes:**
- Risk: Enabling class unserialization can allow remote code execution if cache is compromised.
- Files: `config/cache.php`
- Current mitigation: `serializable_classes: false` - blocks all class unserialization
- Recommendations: Keep disabled (already secure)

**Missing CSRF for API Routes:**
- Risk: If any API routes are added without middleware, they may be vulnerable to CSRF attacks.
- Files: `routes/web.php`
- Current mitigation: Fortify routes use `['web']` middleware which includes CSRF protection
- Recommendations: Ensure all new routes use appropriate middleware

**No Rate Limiting Beyond Fortify:**
- Risk: Application-wide rate limiting is not configured, only Fortify auth endpoints have limiting.
- Files: `config/fortify.php`
- Current mitigation: Fortify includes login throttling (`login` limiter at 5 requests/minute)
- Recommendations: Add application-wide rate limiting via Laravel's `ThrottleRequests` middleware for non-auth routes

**Two-Factor Authentication:**
- Risk: 2FA is enabled but recovery codes must be stored securely - they grant full account access.
- Files: `app/Models/User.php` (uses `TwoFactorAuthenticatable` trait)
- Current mitigation: Recovery codes are hashed in storage
- Recommendations: Ensure users store recovery codes securely (password manager, printed copy)

---

## Performance Concerns

**Database Session Driver:**
- Problem: Default session driver is `database`, causing a database query on every authenticated request.
- Files: `config/session.php`
- Cause: Each page load queries the sessions table
- Improvement path: Use Redis (`SESSION_DRIVER=redis`) for production for better performance

**Database Cache Driver:**
- Problem: Default cache store is `database`, requiring extra queries for cached data.
- Files: `config/cache.php`
- Cause: Database-backed cache adds overhead
- Improvement path: Use Redis or Memcached (`CACHE_STORE=redis`) for production

**No Query Optimization:**
- Problem: No apparent use of query caching or eager loading patterns since minimal data exists
- Files: `app/Models/User.php`
- Cause: Skeleton application has minimal database interactions
- Improvement path: Add N+1 prevention when expanding User model relationships

**Frontend Build:**
- Problem: Vite development server required for hot reload, but production needs `npm run build`.
- Files: `vite.config.js`, `package.json`
- Cause: Tailwind CSS v4 requires build step for production
- Improvement path: Ensure CI/CD runs `npm run build` before deployment

---

## Scalability Limits

**Session Storage:**
- Current capacity: Single-server session storage with file/database drivers
- Limit: Does not support session sharing across multiple application instances
- Scaling path: Use Redis session driver with shared Redis instance for horizontal scaling

**File Storage:**
- Current capacity: Local filesystem storage only
- Limit: Media files not shared between application instances
- Scaling path: Integrate object storage (AWS S3, Google Cloud Storage) via `FILESYSTEM_DISK` env

**Database:**
- Current capacity: SQLite by default (single file, limited concurrency)
- Limit: Not designed for high-concurrency production workloads
- Scaling path: Use MySQL or PostgreSQL with connection pooling for production

**Queue System:**
- Current capacity: Database queue driver by default (`QUEUE_CONNECTION=database`)
- Limit: Synchronous processing overhead, not suitable for high-volume jobs
- Scaling path: Use Redis queue driver (`QUEUE_CONNECTION=redis`) with supervised workers for production

**Static Assets:**
- Current capacity: Vite serves assets during development
- Limit: No CDN integration configured
- Scaling path: Configure Vite to publish to CDN or use Laravel Vapor for automated scaling

---

## Deprecated Patterns & Packages

**No Deprecated Patterns Detected:**
- The codebase uses current Laravel 13, Livewire 4, and Tailwind CSS v4 patterns
- No legacy code patterns detected

**Package Considerations:**

| Package | Version | Status |
|--------|---------|-------|
| laravel/framework | ^13.0 | Current stable (LTS) |
| livewire/livewire | ^4.1 | Current stable |
| livewire/flux | ^2.13.1 | Current stable |
| laravel/fortify | ^1.34 | Current stable |
| tailwindcss | ^4.0.7 | Note: v4 has significant breaking changes from v3 |

**Tailwind CSS v4 Compatibility:**
- Risk: v4 uses new CSS-first configuration (no `tailwind.config.js` by default)
- Files: `resources/css/app.css`
- Current approach: Uses `@import 'tailwindcss'` and `@theme` directive in CSS
- Recommendations: Review Tailwind v4 migration guide if customizing beyond provided theme

---

## Browser Requirements

**Modern Browser Required:**
- The application uses Livewire 4 and Flux UI components that leverage modern browser features
- No explicit browser support matrix found in codebase

**Minimum Expected:**
- Chrome/Edge 88+ (for CSS `:has()` selector support used by Flux)
- Firefox 78+
- Safari 15+ (partial support)
- Modern mobile browsers (iOS 15+, Android modern)

**JavaScript Required:**
- Livewire components require JavaScript to be enabled
- No progressive enhancement for JavaScript-disabled browsers
- Files: `resources/js/app.js` - Loads Livewire

---

## Environment Requirements

**PHP Requirements:**
- **Minimum PHP Version**: 8.3 (strict, `^8.3`)
- Required extensions (implied by Laravel 13):
  - `pdo_sqlite` (for SQLite default)
  - `pdo_mysql` (for MySQL connections)
  - `mbstring`
  - `openssl`
  - `tokenizer`
  - `xml`
  - `curl`

**Node.js Requirements:**
- For frontend build (from `package.json`):
  - Node.js 18+ (for Vite)
  - npm 9+

**Environment Variables Required:**

| Variable | Purpose | Example |
|---------|---------|---------|
| `APP_KEY` | Encryption | Must be set via `php artisan key:generate` |
| `APP_ENV` | Environment | `production` for prod |
| `APP_DEBUG` | Debug mode | `false` for prod |
| `DB_CONNECTION` | Database | `mysql` for production |
| `SESSION_DRIVER` | Sessions | `redis` for production |
| `CACHE_STORE` | Cache | `redis` for production |
| `QUEUE_CONNECTION` | Jobs | `redis` for production |
| `REDIS_*` | Redis config | If using Redis |

**Production Checklist:**
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_KEY` (32-byte key)
- [ ] Configure production database (MySQL/PostgreSQL)
- [ ] Configure Redis for session/cache/queue
- [ ] Configure queue worker supervisor
- [ ] Run `npm run build` for production assets
- [ ] Set up log aggregation
- [ ] Configure health check endpoints

---

## Testing Gaps

**Minimal Test Coverage:**
- What's not tested: No application-specific tests exist beyond authentication feature tests provided by starter kit
- Files: `tests/` directory contains only auth-related tests
- Risk: Any custom business logic will have no test coverage without additional tests
- Priority: High (for any production functionality)

**No Integration Tests:**
- What's not tested: No end-to-end browser tests or integration tests beyond auth flows
- Files: No `tests/Browser/` directory
- Risk: UI interactions cannot be verified automatically
- Priority: Medium

**No Unit Tests:**
- What's not tested: No unit tests for service classes or utilities
- Files: `tests/Unit/` only has example test
- Risk: Business logic breaks go undetected
- Priority: High

---

## Missing Critical Features (for Production)

**Email Configuration:**
- Problem: Mail driver defaults to `log` (writes to storage/logs)
- Files: `config/mail.php`, `.env.example`
- Blocks: Cannot send password reset emails in production
- Fix: Configure SMTP driver in production

**Queue Worker:**
- Problem: No queue worker process management configured
- Files: No supervisor configuration
- Blocks: Background jobs not processed
- Fix: Set up supervisor to run `php artisan queue:work`

**Asset Publishing:**
- Problem: No CI/CD pipeline configured
- Files: No deployment configuration
- Blocks: No automated deployment
- Fix: Configure deployment pipeline

**Log Management:**
- Problem: Logs default to single file (`LOG_STACK=single`)
- Files: `config/logging.php`
- Impact: Difficult to search/analyze in production
- Fix: Use log aggregation service (Papertrail, Datadog, CloudWatch)

---

*Concerns audit: 2026-04-23*