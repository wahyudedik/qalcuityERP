# Implementation Plan

- [x] 1. Write bug condition exploration test
  - **Property 1: Bug Condition** - Plan Gating Validation Failure
  - **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bug exists
  - **DO NOT attempt to fix the test or the code when it fails**
  - **NOTE**: This test encodes the expected behavior - it will validate the fix when it passes after implementation
  - **GOAL**: Surface counterexamples that demonstrate the bug exists
  - **Scoped PBT Approach**: For deterministic bugs, scope the property to the concrete failing case(s) to ensure reproducibility
  - Test implementation details from Bug Condition in design
  - The test assertions should match the Expected Behavior Properties from design
  - Run test on UNFIXED code
  - **EXPECTED OUTCOME**: Test FAILS (this is correct - it proves the bug exists)
  - Document counterexamples found to understand root cause
  - Mark task complete when test is written, run, and failure is documented
  - _Requirements: 1.1, 1.2, 1.3, 1.4_

  - [x] 1.1 Test: Starter plan activates manufacturing module
    - Create test tenant with plan `starter`
    - Send POST request to `/settings/modules` with `modules = ['pos', 'manufacturing']`
    - Assert response is HTTP 422 with validation error mentioning `manufacturing` not allowed
    - **Expected on UNFIXED code**: Request succeeds and `manufacturing` is saved to `enabled_modules` (BUG)
    - Document: "Tenant with starter plan can activate manufacturing module without validation"

  - [x] 1.2 Test: Plan downgrade does not sync enabled_modules
    - Create test tenant with plan `professional` and `enabled_modules = ['pos', 'fleet', 'wms']`
    - Super-admin sends POST to update plan to `starter`
    - Assert `enabled_modules` is updated to only contain modules allowed by starter plan
    - **Expected on UNFIXED code**: `enabled_modules` remains unchanged with `fleet` and `wms` (BUG)
    - Document: "Plan downgrade does not strip disallowed modules from enabled_modules"

  - [x] 1.3 Test: Route access without plan validation
    - Create test tenant with plan `starter` and `enabled_modules = ['pos', 'inventory']`
    - Attempt to access route `/fleet/vehicles` (assuming fleet module route exists)
    - Assert response is HTTP 403 with message about plan upgrade required
    - **Expected on UNFIXED code**: Route is accessible if no middleware blocks it (BUG)
    - Document: "No middleware validates plan access for module routes"

  - [x] 1.4 Test: Legacy plan slug validation
    - Super-admin attempts to update tenant plan to `starter`
    - Assert validation accepts `starter`, `business`, `professional`, `enterprise` slugs
    - **Expected on UNFIXED code**: Validation fails because only accepts `trial,basic,pro,enterprise` (BUG)
    - Document: "TenantController::updatePlan uses legacy slug validation"

- [x] 2. Write preservation property tests (BEFORE implementing fix)
  - **Property 2: Preservation** - Backward Compatibility and Allowed Activations
  - **IMPORTANT**: Follow observation-first methodology
  - Observe behavior on UNFIXED code for non-buggy inputs
  - Write property-based tests capturing observed behavior patterns from Preservation Requirements
  - Property-based testing generates many test cases for stronger guarantees
  - Run tests on UNFIXED code
  - **EXPECTED OUTCOME**: Tests PASS (this confirms baseline behavior to preserve)
  - Mark task complete when tests are written, run, and passing on unfixed code
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6_

  - [x] 2.1 Test: Null enabled_modules backward compatibility
    - Create test tenant with `enabled_modules = null`
    - Verify `isModuleEnabled('manufacturing')` returns true
    - Verify `enabledModules()` returns all modules from `ModuleRecommendationService::ALL_MODULES`
    - Verify tenant can access any module route without 403
    - **Expected on UNFIXED code**: All assertions pass (PRESERVE)

  - [x] 2.2 Test: Allowed module activation succeeds
    - Create test tenant with plan `business`
    - Send POST to `/settings/modules` with `modules = ['pos', 'crm', 'helpdesk', 'subscription_billing']`
    - Assert response is HTTP 200 and `enabled_modules` is updated correctly
    - **Expected on UNFIXED code**: Request succeeds (PRESERVE)

  - [x] 2.3 Test: Super-admin has full access
    - Create super-admin user
    - Attempt to access any module route (e.g., `/fleet/vehicles`, `/manufacturing/bom`)
    - Assert no 403 response
    - **Expected on UNFIXED code**: Super-admin can access all routes (PRESERVE)

  - [x] 2.4 Test: Professional/Enterprise can activate advanced modules
    - Create test tenant with plan `professional`
    - Send POST to `/settings/modules` with `modules = ['manufacturing', 'fleet', 'wms']`
    - Assert response is HTTP 200 and modules are saved
    - **Expected on UNFIXED code**: Request succeeds (PRESERVE)

  - [x] 2.5 Test: isModuleEnabled respects enabled_modules array
    - Create test tenant with `enabled_modules = ['pos', 'inventory']`
    - Assert `isModuleEnabled('pos')` returns true
    - Assert `isModuleEnabled('manufacturing')` returns false
    - **Expected on UNFIXED code**: Assertions pass (PRESERVE)

  - [x] 2.6 Test: Expired tenant redirected by CheckTenantActive
    - Create test tenant with `plan_expires_at` in the past
    - Attempt to access any route
    - Assert redirected to subscription expired page
    - **Expected on UNFIXED code**: Redirect happens (PRESERVE)

- [x] 3. Fix for package module validation

  - [x] 3.1 Create PlanModuleMap service
    - Create file `app/Services/PlanModuleMap.php`
    - Define constant `PLAN_MODULES` mapping plan slugs to allowed module keys
    - Implement `getAllowedModules(string $planSlug): array`
    - Implement `isModuleAllowedForPlan(string $moduleKey, string $planSlug): bool`
    - Implement `filterAllowedModules(array $modules, string $planSlug): array`
    - Implement `getDisallowedModules(array $modules, string $planSlug): array`
    - Map plan slugs: `starter`, `business`, `professional`, `enterprise`, `trial`
    - Starter: pos, inventory, sales, invoicing, reports
    - Business: starter + purchasing, crm, accounting, budget, helpdesk, commission, consignment, subscription_billing, reimbursement
    - Professional: all modules except telecom, hotel, fnb, spa (industry-specific)
    - Enterprise: all modules
    - Trial: same as starter
    - _Bug_Condition: isBugCondition(input) where input.requestedModules contains module NOT IN getAllowedModules(tenant.plan_slug)_
    - _Expected_Behavior: getAllowedModules returns correct subset for each plan_
    - _Preservation: Returns all modules for null/unknown plan slugs (backward compat)_
    - _Requirements: 2.3_

  - [x] 3.2 Add plan validation to ModuleSettingsController::update()
    - Import `PlanModuleMap` service
    - Get tenant's plan slug from `$tenant->subscriptionPlan->slug ?? $tenant->plan`
    - Call `PlanModuleMap::getDisallowedModules($newModules, $planSlug)`
    - If disallowed modules exist, return validation error with HTTP 422
    - Error message: "Modul berikut tidak diizinkan untuk paket {plan}: {modules}"
    - Skip validation if `$tenant->enabled_modules === null` (backward compat)
    - _Bug_Condition: isBugCondition(input) where tenant with plan X requests module Y not in allowed list_
    - _Expected_Behavior: Request rejected with HTTP 422 and clear error message_
    - _Preservation: Null enabled_modules tenants bypass validation_
    - _Requirements: 2.1_

  - [x] 3.3 Sync enabled_modules in TenantController::updatePlan()
    - Update validation rule to accept new plan slugs: `'plan' => 'required|in:trial,starter,business,professional,enterprise'`
    - After `$tenant->update($data)`, check if `$tenant->enabled_modules !== null`
    - If not null, get new plan slug from `$data['plan']`
    - Call `PlanModuleMap::filterAllowedModules($tenant->enabled_modules, $newPlanSlug)`
    - Update tenant: `$tenant->update(['enabled_modules' => $filteredModules])`
    - Log the sync action with modules removed
    - _Bug_Condition: isBugCondition(input) where plan downgrade leaves disallowed modules in enabled_modules_
    - _Expected_Behavior: enabled_modules automatically filtered to only allowed modules_
    - _Preservation: Null enabled_modules tenants not affected_
    - _Requirements: 2.2_

  - [x] 3.4 Create CheckModulePlanAccess middleware
    - Create file `app/Http/Middleware/CheckModulePlanAccess.php`
    - Implement `handle(Request $request, Closure $next, string $moduleKey): Response`
    - Check if user is authenticated, redirect to login if not
    - Check if user is super-admin, allow access if true
    - Get tenant from `$request->user()->tenant`
    - If `$tenant->enabled_modules === null`, allow access (backward compat)
    - Get plan slug from `$tenant->subscriptionPlan->slug ?? $tenant->plan`
    - Call `PlanModuleMap::isModuleAllowedForPlan($moduleKey, $planSlug)`
    - If not allowed, return HTTP 403 with message: "Modul {module} memerlukan upgrade paket"
    - For JSON requests, return JSON response
    - For web requests, redirect to subscription upgrade page with module context
    - _Bug_Condition: isBugCondition(input) where tenant accesses route for disallowed module_
    - _Expected_Behavior: HTTP 403 with upgrade message_
    - _Preservation: Super-admin and null enabled_modules bypass check_
    - _Requirements: 2.4_

  - [x] 3.5 Register CheckModulePlanAccess middleware
    - Open `bootstrap/app.php` or `app/Http/Kernel.php` (depending on Laravel version)
    - Register middleware alias: `'check.module.plan' => \App\Http\Middleware\CheckModulePlanAccess::class`
    - Document usage example in comments: `Route::middleware('check.module.plan:manufacturing')->group(...)`
    - _Requirements: 2.4_

  - [x] 3.6 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - Plan Gating Validation Success
    - **IMPORTANT**: Re-run the SAME test from task 1 - do NOT write a new test
    - The test from task 1 encodes the expected behavior
    - When this test passes, it confirms the expected behavior is satisfied
    - Run bug condition exploration test from step 1
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed)
    - _Requirements: Expected Behavior Properties from design_

  - [x] 3.7 Verify preservation tests still pass
    - **Property 2: Preservation** - No Regressions
    - **IMPORTANT**: Re-run the SAME tests from task 2 - do NOT write new tests
    - Run preservation property tests from step 2
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)
    - Confirm all tests still pass after fix (no regressions)

- [x] 4. Checkpoint - Ensure all tests pass
  - Run all exploration tests - should pass
  - Run all preservation tests - should pass
  - Run full test suite to catch any unexpected regressions
  - If any test fails, investigate and fix before proceeding
  - Ask user if questions arise about edge cases or implementation details
