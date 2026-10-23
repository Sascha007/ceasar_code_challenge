# Test Documentation

This document describes the test setup, test structure, and how to run tests for this project.

## Test Structure

The test suite is organized into the following categories:

### Unit Tests (`tests/Unit/`)

Unit tests verify individual components in isolation:

- **Caesar/CaesarServiceTest.php** - Tests for Caesar cipher encoding/decoding service
- **Models/** - Tests for Eloquent models
  - **CompetitionTest.php** - Competition model lifecycle and state management
  - **TeamTest.php** - Team model methods, relationships, and state transitions
  - **PuzzleTest.php** - Puzzle encryption, validation, and auto-generation
  - **SubmissionTest.php** - Submission model and query scopes
- **Services/** - Tests for service classes
  - **QrCodeServiceTest.php** - QR code generation and PDF creation

### Feature Tests (`tests/Feature/`)

Feature tests verify higher-level functionality and user workflows:

- **Auth/** - Authentication features (login, registration, password reset, etc.)
- **Admin/** - Admin panel features
  - **CompetitionManagementTest.php** - Competition CRUD operations and lifecycle management
- **Competition/** - Competition features
  - **CompetitionFlowTest.php** - End-to-end competition workflows and team submissions
- **ProfileTest.php** - User profile management

## Test Factories

Factories provide convenient ways to generate test data. Located in `database/factories/`:

- **CompetitionFactory** - States: `ready()`, `running()`, `finished()`
- **TeamFactory** - States: `ready()`, `solved()`
- **PuzzleFactory** - Modifiers: `withPlaintext()`, `withShift()`
- **SubmissionFactory** - States: `correct()`, `incorrect()`
- **UserFactory** - State: `unverified()`

### Example Usage

```php
// Create a running competition with two teams
$competition = Competition::factory()->running()->create();
$team1 = Team::factory()->ready()->create(['competition_id' => $competition->id]);
$team2 = Team::factory()->solved()->create(['competition_id' => $competition->id]);

// Create a puzzle with specific text
$puzzle = Puzzle::factory()
    ->withPlaintext('Hello World')
    ->withShift(3)
    ->create(['team_id' => $team1->id]);
```

## Running Tests

### Run all tests
```bash
php artisan test
```

### Run specific test suite
```bash
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

### Run specific test file
```bash
php artisan test tests/Unit/Models/TeamTest.php
```

### Run with coverage (requires Xdebug)
```bash
php artisan test --coverage
```

### Run with minimum coverage threshold
```bash
php artisan test --coverage --min=70
```

### Generate HTML coverage report
```bash
vendor/bin/phpunit --coverage-html coverage-report
```

## Code Coverage

Code coverage is automatically generated in the CI pipeline for pull requests and pushes to the main branch.

### Coverage Configuration

Coverage settings are defined in `phpunit.xml`:
- Source directory: `app/`
- Coverage reports are generated for all code in the `app` directory

### CI Coverage Job

The GitHub Actions workflow includes a dedicated coverage job that:
1. Runs tests with Xdebug enabled
2. Generates HTML and Clover XML coverage reports
3. Uploads reports as artifacts (retained for 30 days)
4. Enforces minimum 70% coverage threshold

### Viewing Coverage Reports

Coverage reports are available as artifacts in the GitHub Actions workflow runs:
1. Go to the Actions tab in GitHub
2. Select a workflow run
3. Download the `coverage-report` artifact
4. Open `index.html` in a browser

## Writing Tests

### Test Naming Convention

Follow PHPUnit conventions:
- Test method names should start with `test_`
- Use descriptive names: `test_team_can_mark_itself_as_ready()`
- Use snake_case for test method names

### Using RefreshDatabase

Most tests should use the `RefreshDatabase` trait to ensure a clean database state:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_example(): void
    {
        // Test code here
    }
}
```

### Assertions

Use specific assertions for better error messages:
- `assertDatabaseHas()` / `assertDatabaseMissing()` for database checks
- `assertCount()` for collection/array counts
- `assertInstanceOf()` for type checks
- `assertTrue()` / `assertFalse()` for boolean checks

## Test Coverage Goals

The project aims for the following coverage targets:

1. **Unit Tests**
   - ✅ CaesarService: 100%
   - ✅ Models: >90% (Competition, Team, Puzzle, Submission)
   - ✅ Services: >80% (QrCodeService)

2. **Feature Tests**
   - ✅ Authentication: Complete
   - ✅ Admin Features: Complete
   - ✅ Competition Flow: Complete

3. **Integration Tests**
   - ✅ End-to-End Competition Flow
   - ✅ Team Submission Flow
   - ✅ Admin Management Flow

## Continuous Integration

Tests run automatically on:
- Pull requests to `main`
- Pushes to `main`

The CI pipeline includes:
1. **Tests Job** - Runs all tests
2. **Coverage Job** - Generates coverage reports
3. **Shared Hosting Check** - Verifies production build

## Current Test Statistics

- **Total Tests**: 114
- **Total Assertions**: 288
- **Unit Tests**: 69
- **Feature Tests**: 45

All tests are currently passing ✅
