# Installer Package Bootstrap Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Update `php artisan medsdn:install` so a fresh installation automatically bootstraps all first-party `packages/medsdn` packages using an explicit, idempotent package-install pipeline.

**Architecture:** Keep the existing installer command as the entry point, but move package-specific post-install behavior into a dedicated registry-driven bootstrap layer inside the Installer package. Base seeders remain responsible for core first-run data, while package bootstrappers handle safe post-seed initialization and package install commands in a deterministic order.

**Tech Stack:** Laravel 11, Artisan console commands, Installer package seeders, Pest tests.

---

### Task 1: Add installer acceptance coverage for package bootstrap registration

**Files:**
- Create: `packages/medsdn/Installer/tests/Feature/PackageBootstrapPipelineTest.php`
- Modify: `packages/medsdn/Installer/tests/InstallerTestCase.php`

**Step 1: Write the failing test**

Add tests that assert:
- the installer defines a package bootstrap pipeline/registry
- the registry includes every first-party package in one of three categories:
  - schema-only
  - seeded/bootstrap
  - command-driven
- expected command-driven packages include current install/index commands such as `medsdn-graphql:install`, `medsdn-api-platform:install`, `medsdn-api:generate-key`, and `product:price-rule:index`

**Step 2: Run test to verify it fails**

Run: `php artisan test packages/medsdn/Installer/tests/Feature/PackageBootstrapPipelineTest.php`

Expected: FAIL because no such registry exists yet.

**Step 3: Write minimal implementation scaffold**

Add the minimal classes/interfaces/constants needed for the test to load the registry.

**Step 4: Run test to verify it passes**

Run: `php artisan test packages/medsdn/Installer/tests/Feature/PackageBootstrapPipelineTest.php`

Expected: PASS

### Task 2: Extract package bootstrap pipeline from the installer command

**Files:**
- Create: `packages/medsdn/Installer/src/Support/PackageBootstrap/PackageBootstrapStep.php`
- Create: `packages/medsdn/Installer/src/Support/PackageBootstrap/PackageBootstrapRegistry.php`
- Create: `packages/medsdn/Installer/src/Support/PackageBootstrap/PackageBootstrapRunner.php`
- Modify: `packages/medsdn/Installer/src/Console/Commands/Installer.php`

**Step 1: Write the failing test**

Add tests for the runner that assert:
- steps execute in deterministic order
- skipped steps are reported but do not fail the install
- failing steps surface useful context

**Step 2: Run test to verify it fails**

Run: `php artisan test packages/medsdn/Installer/tests/Feature/PackageBootstrapPipelineTest.php --filter runner`

Expected: FAIL because the runner does not exist.

**Step 3: Write minimal implementation**

Implement:
- a step DTO/config object
- a registry that returns the ordered steps
- a runner that executes steps and reports status
- command integration in `Installer::handle()` after base seeding and before final success messaging

**Step 4: Run test to verify it passes**

Run: `php artisan test packages/medsdn/Installer/tests/Feature/PackageBootstrapPipelineTest.php --filter runner`

Expected: PASS

### Task 3: Define full first-party package coverage in the registry

**Files:**
- Modify: `packages/medsdn/Installer/src/Support/PackageBootstrap/PackageBootstrapRegistry.php`
- Modify: `packages/medsdn/Installer/src/Database/Seeders/DatabaseSeeder.php`

**Step 1: Write the failing test**

Add assertions that every directory under `packages/medsdn` except infrastructure-only packages is accounted for in the registry or documented as schema-only.

**Step 2: Run test to verify it fails**

Run: `php artisan test packages/medsdn/Installer/tests/Feature/PackageBootstrapPipelineTest.php --filter coverage`

Expected: FAIL until all packages are registered.

**Step 3: Write minimal implementation**

Register:
- schema-only packages that only rely on migrations
- packages already covered by base seeders
- command-driven packages for API/install/index flows
- any newly required seeded/bootstrap packages

Keep `Installer\Database\Seeders\DatabaseSeeder` focused on real first-run data only.

**Step 4: Run test to verify it passes**

Run: `php artisan test packages/medsdn/Installer/tests/Feature/PackageBootstrapPipelineTest.php --filter coverage`

Expected: PASS

### Task 4: Add command-driven bootstrap for installable packages

**Files:**
- Modify: `packages/medsdn/Installer/src/Support/PackageBootstrap/PackageBootstrapRegistry.php`
- Modify: `packages/medsdn/Installer/src/Support/PackageBootstrap/PackageBootstrapRunner.php`
- Modify: `packages/medsdn/Installer/src/Console/Commands/Installer.php`

**Step 1: Write the failing test**

Add tests that assert the runner invokes command-based steps with the expected command names/options for:
- `medsdn-graphql:install`
- `medsdn-api-platform:install`
- `medsdn-api:generate-key`
- `product:price-rule:index`

**Step 2: Run test to verify it fails**

Run: `php artisan test packages/medsdn/Installer/tests/Feature/PackageBootstrapPipelineTest.php --filter command`

Expected: FAIL because commands are not wired yet.

**Step 3: Write minimal implementation**

Implement command step execution with:
- `Artisan::call(...)`
- optional command-exists guarding
- structured status messages
- idempotent behavior for fresh installs

**Step 4: Run test to verify it passes**

Run: `php artisan test packages/medsdn/Installer/tests/Feature/PackageBootstrapPipelineTest.php --filter command`

Expected: PASS

### Task 5: Update installer messaging and success output

**Files:**
- Modify: `packages/medsdn/Installer/src/Console/Commands/Installer.php`
- Modify: `packages/medsdn/Installer/src/Resources/lang/en/app.php`
- Modify: `packages/medsdn/Installer/src/Resources/views/installer/index.blade.php`

**Step 1: Write the failing test**

Add assertions that installer output references MedSDN naming and mentions package bootstrap progress instead of the old Bagisto wording.

**Step 2: Run test to verify it fails**

Run: `php artisan test packages/medsdn/Installer/tests/Feature/PackageBootstrapPipelineTest.php --filter messaging`

Expected: FAIL because old output/messages still exist.

**Step 3: Write minimal implementation**

Update console messages and any installer copy touched by this flow to mention MedSDN package bootstrap accurately.

**Step 4: Run test to verify it passes**

Run: `php artisan test packages/medsdn/Installer/tests/Feature/PackageBootstrapPipelineTest.php --filter messaging`

Expected: PASS

### Task 6: Run targeted verification

**Files:**
- Test: `packages/medsdn/Installer/tests/Feature/PackageBootstrapPipelineTest.php`
- Test: `packages/medsdn/Installer/tests/Feature/InstallerSecurityTest.php`

**Step 1: Run focused tests**

Run:
- `php artisan test packages/medsdn/Installer/tests/Feature/PackageBootstrapPipelineTest.php`
- `php artisan test packages/medsdn/Installer/tests/Feature/InstallerSecurityTest.php`

**Step 2: Run source-level bootstrap verification**

Run a source check confirming:
- all first-party packages are represented in the registry
- legacy `Bagisto` install wording is removed from the touched installer flow

**Step 3: Document any blocked runtime verification**

If full `php artisan medsdn:install` cannot be executed because the environment bootstraps into DB/extension failures, record that clearly in the close-out.
