# Testing Patterns

**Analysis Date:** 2026-04-23

## Test Framework

**Primary:**
- **Pest PHP** v4.6 - testing framework (from `composer.json`)
- **PHPUnit** v12.x - underlying test runner
- **Pest Laravel Plugin** v4.1 - Laravel integration

**Configuration File:**
- `phpunit.xml` - at project root
- Test directories: `tests/Unit/`, `tests/Feature/`

**Run Commands:**
```bash
php artisan test                    # Run all tests
php artisan test --filter=testName # Filter by name
php artisan test --compact        # Compact output
```

## Test File Organization

**Location Pattern:**
- Unit tests: `tests/Unit/`
- Feature tests: `tests/Feature/`

**Naming:**
- Suffix `Test.php` on files: `ExampleTest.php`
- Directory mirrors feature structure: `tests/Feature/Auth/AuthenticationTest.php`

**Structure:**
```
tests/
├── Pest.php              # Global configuration
├── TestCase.php         # Base test case class
├── Unit/
│   └── ExampleTest.php
└── Feature/
    ├── Auth/
    │   ├── AuthenticationTest.php
    │   ├── PasswordConfirmationTest.php
    │   ├── PasswordResetTest.php
    │   ├── TwoFactorChallengeTest.php
    │   ├── EmailVerificationTest.php
    │   └── RegistrationTest.php
    ├── Settings/
    │   ├── ProfileUpdateTest.php
    │   └── SecurityTest.php
    ├── DashboardTest.php
    └── ExampleTest.php
```

## Test Structure

**Base Configuration** (`tests/Pest.php`):
```php
pest()->extend(TestCase::class)
    ->in('Feature');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});
```

**Base TestCase** (`tests/TestCase.php`):
```php
abstract class TestCase extends BaseTestCase
{
    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
```

**Simple Test** (`tests/Unit/ExampleTest.php`):
```php
test('that true is true', function () {
    expect(true)->toBeTrue();
});
```

**Feature Test** (`tests/Feature/ExampleTest.php`):
```php
test('returns a successful response', function () {
    $response = $this->get(route('home'));
    $response->assertOk();
});
```

## Test Patterns

**Authentication Tests:**
```php
test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});
```

**Livewire Component Tests:**
```php
test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->email)->toEqual('test@example.com');
});
```

**Two-Factor Auth Tests:**
```php
test('users with two factor enabled are redirected to two factor challenge', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create();
    // ...
});
```

## Factories

**Location:** `database/factories/UserFactory.php`

**Usage:**
```php
$user = User::factory()->create();
$user = User::factory()->unverified()->create();
$user = User::factory()->withTwoFactor()->create();
```

**Custom States:**
```php
public function withTwoFactor(): static
{
    return $this->state(fn (array $attributes) => [
        'two_factor_secret' => encrypt('secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
        'two_factor_confirmed_at' => now(),
    ]);
}
```

## Database, Fixtures & Mocking

**Database:**
- In-memory SQLite: `DB_DATABASE=:memory:` in `phpunit.xml`
- Use RefreshDatabase trait for tests needing DB (configured but disabled in `Pest.php`)

**Test User Password:**
- Default password: `'password'` (set in `UserFactory`)
- Bcrypt rounds in tests: `BCRYPT_ROUNDS=4`

**Mocking:**
- Standard Mockery for external dependencies
- Pest's `mock()` helper available

## Test Categories

**Feature Tests:**
- HTTP tests with `$this->get()`, `$this->post()`
- Route assertions: `assertOk()`, `assertRedirect()`, `assertSessionHasErrorsIn()`
- Auth assertions: `assertAuthenticated()`, `assertGuest()`

**Unit Tests:**
- Simple functional tests
- No HTTP/mouting needed

**Livewire Tests:**
- Use `Livewire::test(Component::class)`
- Chain methods: `->set()`, `->call()`, `->assertHasNoErrors()`

## Coverage

**Enforced:** No explicit coverage target in `phpunit.xml`

**Test Scope:**
- `app/` directory is tested (configured in `<source><include>`)

**Environment Variables (from `phpunit.xml`):**
```xml
<env name="APP_ENV" value="testing"/>
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
<env name="CACHE_STORE" value="array"/>
<env name="SESSION_DRIVER" value="array"/>
<env name="QUEUE_CONNECTION" value="sync"/>
```

## CI/Testing Integration

**Composer Scripts:**
```bash
composer test        # Full: lint check + tests
composer ci:check   # CI pipeline (same as test)
```

**Script Definition:**
```json
"scripts": {
    "test": [
        "@php artisan config:clear --ansi",
        "@lint:check",
        "@php artisan test"
    ]
}
```

---

*Testing analysis: 2026-04-23*