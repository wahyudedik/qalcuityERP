# Implementation Plan

- [x] 1. Write bug condition exploration test
  - **Property 1: Bug Condition** - Save Industry HTTP 500 on Missing `skipped` Column
  - **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bug exists
  - **DO NOT attempt to fix the test or the code when it fails**
  - **NOTE**: This test encodes the expected behavior - it will validate the fix when it passes after implementation
  - **GOAL**: Surface counterexamples that demonstrate the bug exists
  - **Scoped PBT Approach**: Scope the property to the concrete failing cases — POST `/onboarding/save-industry` with any valid industry value (retail, restaurant, hotel, construction, agriculture, manufacturing, services) and any valid business_size (micro, small, medium, large) for an authenticated user whose tenant has no `skipped` column in `onboarding_profiles`
  - Create a PHPUnit feature test `tests/Feature/OnboardingBugConditionTest.php`
  - Simulate POST request to `/onboarding/save-industry` as authenticated user with valid payload `{industry: 'retail', business_size: 'small'}`
  - Assert response status is 200 AND response is valid JSON AND `response.success === true`
  - Also test `skip()` endpoint: POST `/onboarding/skip` → assert redirect to dashboard without SQL error
  - Also test `SampleDataGeneratorService::getTemplates('retail')` → assert returns non-empty array
  - Run test on UNFIXED code (before migration and seeder)
  - **EXPECTED OUTCOME**: Test FAILS with SQL error `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'skipped'` and `getTemplates` returns `[]` (this is correct - it proves the bug exists)
  - Document counterexamples found: e.g., `saveIndustry({industry:'retail', business_size:'small'})` returns HTTP 500 with HTML body instead of JSON
  - Mark task complete when test is written, run, and failure is documented
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

- [x] 2. Add `skipped` column via new migration
  - Create migration file `database/migrations/2026_04_06_000013_add_skipped_to_onboarding_profiles.php`
  - `up()`: `$table->boolean('skipped')->default(false)->after('completed_at');`
  - `down()`: `$table->dropColumn('skipped');`
  - Run `php artisan migrate` to apply the migration
  - _Bug_Condition: isBugCondition(X) where OnboardingProfile.columnExists('skipped') = FALSE_
  - _Expected_Behavior: updateOrCreate() executes without SQL error, returns HTTP 200 JSON_
  - _Requirements: 2.1, 2.2_

- [x] 3. Remove duplicate `save-industry` route
  - Open `routes/web.php`
  - Locate the three duplicate `Route::post('/save-industry', ...)` definitions around line 2893
  - Delete two of the three duplicate lines, leaving exactly one definition
  - Verify with `php artisan route:list --name=onboarding.save-industry` that only one route is registered
  - _Requirements: 2.1, 2.3_

- [x] 4. Create `SampleDataTemplateSeeder`
  - Create `database/seeders/SampleDataTemplateSeeder.php`
  - Seed one active template (`is_active = true`) for each of the 7 industries: retail, restaurant, hotel, construction, agriculture, manufacturing, services
  - Each template must include `template_name`, `description`, `modules_included` (array), `data_config` (array), `is_active = true`, `usage_count = 0`
  - Use `SampleDataTemplate::firstOrCreate(['industry' => ..., 'template_name' => ...], [...])` to make seeder idempotent
  - Register seeder in `DatabaseSeeder::run()`: add `SampleDataTemplateSeeder::class` to the `$this->call([])` array
  - Run `php artisan db:seed --class=SampleDataTemplateSeeder` to verify it executes without errors
  - _Requirements: 2.4, 2.5_

- [x] 5. Fix checking — verify bug condition exploration test now passes
  - **Property 1: Expected Behavior** - Save Industry Returns Valid JSON After Fix
  - **IMPORTANT**: Re-run the SAME test from task 1 — do NOT write a new test
  - The test from task 1 encodes the expected behavior
  - When this test passes, it confirms the expected behavior is satisfied
  - Run `php artisan test tests/Feature/OnboardingBugConditionTest.php`
  - **EXPECTED OUTCOME**: Test PASSES — `saveIndustry()` returns HTTP 200 with `{"success": true, "next_step": "..."}`, `skip()` redirects to dashboard, `getTemplates('retail')` returns non-empty array
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

- [x] 6. Write preservation property tests and verify they pass
  - **Property 2: Preservation** - Non-Buggy Input Behavior Unchanged
  - **IMPORTANT**: Follow observation-first methodology
  - Create `tests/Feature/OnboardingPreservationTest.php`
  - Observe on UNFIXED code (or reason from code): POST with invalid industry → HTTP 422; unauthenticated request → redirect to login; completed-onboarding user → redirected to dashboard
  - Write property-based tests covering the non-bug-condition domain:
    - For all invalid industry strings (not in enum) → `saveIndustry()` SHALL return HTTP 422
    - For all unauthenticated requests to `/onboarding/*` → SHALL redirect to login (middleware `auth` active)
    - For user with `completed_at` already set → `index()` SHALL NOT redirect to wizard
    - `generateSampleData()` for supported industries (retail, restaurant, hotel, construction, agriculture) SHALL return `success: true` with `records_created > 0`
  - Run tests on FIXED code
  - **EXPECTED OUTCOME**: All preservation tests PASS (confirms no regressions)
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6_

- [x] 7. Checkpoint — ensure all tests pass
  - Run full test suite: `php artisan test --filter=Onboarding`
  - Confirm Property 1 (bug condition) test passes
  - Confirm Property 2 (preservation) tests pass
  - Confirm no other tests regressed
  - Ensure all tests pass; ask the user if questions arise
