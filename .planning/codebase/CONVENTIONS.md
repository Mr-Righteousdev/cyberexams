# Coding Conventions

**Analysis Date:** 2026-04-23

## Code Style

**Formatter:**
- **Laravel Pint** v1.27 - primary code formatter
- Configuration: `pint.json` with `"preset": "laravel"`
- Run via: `vendor/bin/pint` or `composer lint`
- Check via: `composer lint:check`

**Editor Settings:**
- **EditorConfig** - `.editorconfig` at project root
- **Charset:** UTF-8
- **Indentation:** 4 spaces (not tabs)
- **Line endings:** LF
- **Final newline:** Inserted
- **Trailing whitespace:** Trimmed

## Naming Patterns

**Classes:**
- PascalCase for all class names: `User`, `CreateNewUser`, `Profile`
- Controller suffix for HTTP controllers: `Controller.php`
- Action suffix for action classes: `CreateNewUser.php`, `ResetUserPassword.php`
- Trait suffix for traits: `PasswordValidationRules.php`, `ProfileValidationRules.php`

**Files:**
- Match class name exactly: `app/Models/User.php` → `class User`
- Lowercase with dashes for configs: `pint.json`, `phpunit.xml`

**Variables and Methods:**
- camelCase: `$user`, `$name`, `updateProfileInformation()`
- Property names match database columns: `$this->name`, `$this->email`

**Routes:**
- kebab-case for route names: `profile.edit`, `security.edit`
- kebab-case for route paths: `settings/profile`, `settings/security`

## PHP/Laravel Standards

**PHP Version:**
- PHP 8.3+ required (from `composer.json`)

**Framework Versions:**
- Laravel Framework v13.0
- Laravel Fortify v1.34
- Livewire v4.1
- Livewire Flux v2.13.1

**Eloquent Models:**
```php
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;
}
```
- Use PHP 8 attributes for `#[Fillable]` and `#[Hidden]`
- Add `@use HasFactory<FooFactory>` PHPDoc for factory type safety

**Livewire Components:**
```php
#[Title('Profile settings')]
class Profile extends Component
{
    use ProfileValidationRules;

    #[Computed]
    public function hasUnverifiedEmail(): bool { }
}
```
- Use `#[Title]` attribute for page titles
- Use `#[Computed]` attribute for cached computed properties

**Validation Rules:**
- Extract to concern traits: `app/Concerns/PasswordValidationRules.php`
- Use trait composition: `use PasswordValidationRules, ProfileValidationRules;`
- Return rule arrays from methods: `protected function passwordRules(): array`

**Actions (Fortify):**
- Implement contracts: `implements CreatesNewUsers`
- Place in `app/Actions/Fortify/`
- Use facadeValidator: `Validator::make($input, [...])->validate()`

**Service Providers:**
- Register in `app/Providers/`
- Separate `register()` and `boot()` methods
- Use private methods for configuration: `private function configureActions(): void`

**Routing:**
- Group routes with middleware: `Route::middleware(['auth'])->group(function () { ... })`
- Use route model binding where applicable
- Named routes with `->name('route.name')`

## Import Organization

**Order (by Pint Laravel preset):**
1. Laravel/Fortify imports
2. App imports (`App\Models\`, `App\Actions\`, `App\Concerns\`)
3. Vendor imports
4. PHP built-ins

**No shorthand imports** - always use full namespaces in imports.

## Type Declarations

**Required everywhere:**
- Return types: `public function create(array $input): User`
- Parameter types: `public string $name = ''`
- Nullable: `?string $path = null`

## Error Handling

**Pattern:**
- Validate with `Validator::make(...)->validate()` throws on failure
- Use `$user->fill($validated)` for mass assignment
- Flash messages: `Flux::toast(variant: 'success', text: __('Profile updated.'))`

---

*Convention analysis: 2026-04-23*