# Identifier Rebrand Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Build a repeatable codemod that renames `bagisto_asset(...)` to `medsdn_asset(...)` and `Webkul\\...` references to `Medsdn\\...`, starting with `Admin`, while keeping a short-lived compatibility path during rollout.

**Architecture:** A single PHP codemod script in `scripts/` will scan a controlled file set, apply deterministic transformations per file type, and support `--dry-run` and `--write`. The rollout starts by adding the new helper, proving behavior with tests, then applying the codemod to `Admin`, followed by acceptance searches and autoload verification.

**Tech Stack:** PHP 8.2, Laravel, Blade, Composer PSR-4 autoload, Pest/PHPUnit, ripgrep

---

### Task 1: Add Failing Acceptance Coverage For New Identifiers

**Files:**
- Modify: `tests/Unit/Project/PackagePathBrandingTest.php`
- Modify: `packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php`
- Create: `tests/Unit/Project/IdentifierCodemodAcceptanceTest.php`

**Step 1: Write the failing test**

Add assertions that the `Admin` scope no longer relies on `bagisto_asset(` in targeted layout files and that repo configuration can resolve `Medsdn\\...` mappings after codemod execution.

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Unit/Project/IdentifierCodemodAcceptanceTest.php`

Expected: FAIL because `medsdn_asset()` and `Medsdn\\...` mappings do not exist yet.

**Step 3: Write minimal implementation expectation updates**

Add the exact residue assertions:

- `packages/medsdn/Admin/src/Resources/views/components/layouts/**/*.blade.php` should prefer `medsdn_asset(`
- project autoload should eventually include `Medsdn\\Admin\\`

**Step 4: Run test to verify it still fails for the right reason**

Run: `php artisan test tests/Unit/Project/IdentifierCodemodAcceptanceTest.php`

Expected: FAIL with missing helper and/or missing autoload mappings, not syntax errors.

**Step 5: Commit**

```bash
git add tests/Unit/Project/IdentifierCodemodAcceptanceTest.php tests/Unit/Project/PackagePathBrandingTest.php packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php
git commit -m "test: add acceptance coverage for identifier rebrand"
```

### Task 2: Add Compatibility Helper Before Large Refactor

**Files:**
- Modify: `packages/medsdn/Theme/src/Http/helpers.php`
- Test: `tests/Unit/Project/IdentifierCodemodAcceptanceTest.php`

**Step 1: Write the failing test**

Add a unit assertion that both helpers exist and resolve through the same theme URL logic:

```php
expect(function_exists('medsdn_asset'))->toBeTrue();
expect(medsdn_asset('images/logo.svg'))->toBe(bagisto_asset('images/logo.svg'));
```

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Unit/Project/IdentifierCodemodAcceptanceTest.php --filter=helper`

Expected: FAIL because `medsdn_asset()` is undefined.

**Step 3: Write minimal implementation**

In `packages/medsdn/Theme/src/Http/helpers.php`:

- add `medsdn_asset()` using the same implementation as `bagisto_asset()`
- keep `bagisto_asset()` as an alias calling `medsdn_asset()`

**Step 4: Run test to verify it passes**

Run: `php artisan test tests/Unit/Project/IdentifierCodemodAcceptanceTest.php --filter=helper`

Expected: PASS

**Step 5: Commit**

```bash
git add packages/medsdn/Theme/src/Http/helpers.php tests/Unit/Project/IdentifierCodemodAcceptanceTest.php
git commit -m "feat: add medsdn asset helper with compatibility alias"
```

### Task 3: Build Dry-Run Codemod Script

**Files:**
- Create: `scripts/rebrand-identifiers.php`
- Create: `tests/Unit/Project/IdentifierCodemodAcceptanceTest.php`

**Step 1: Write the failing test**

Add a test around the script output for `--scope=admin --dry-run`:

```php
$output = shell_exec('php scripts/rebrand-identifiers.php --scope=admin --dry-run');
expect($output)->toContain('DRY RUN');
expect($output)->toContain('bagisto_asset');
expect($output)->toContain('Webkul');
```

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Unit/Project/IdentifierCodemodAcceptanceTest.php --filter=dry`

Expected: FAIL because script does not exist.

**Step 3: Write minimal implementation**

Implement `scripts/rebrand-identifiers.php` with:

- argument parsing for `--scope` and `--dry-run` / `--write`
- path collection for `admin` scope
- file filtering by extension
- no-write reporting mode
- counters for changed files and replacement types

**Step 4: Run test to verify it passes**

Run: `php artisan test tests/Unit/Project/IdentifierCodemodAcceptanceTest.php --filter=dry`

Expected: PASS

**Step 5: Commit**

```bash
git add scripts/rebrand-identifiers.php tests/Unit/Project/IdentifierCodemodAcceptanceTest.php
git commit -m "feat: add dry-run identifier rebrand codemod"
```

### Task 4: Implement Write-Mode Transform Rules

**Files:**
- Modify: `scripts/rebrand-identifiers.php`
- Modify: `composer.json`
- Modify: `packages/medsdn/Admin/composer.json`
- Modify: `packages/medsdn/Admin/src/**/*.php`
- Modify: `packages/medsdn/Admin/src/**/*.blade.php`
- Test: `tests/Unit/Project/IdentifierCodemodAcceptanceTest.php`

**Step 1: Write the failing test**

Add assertions that write-mode updates:

- `bagisto_asset(` → `medsdn_asset(`
- `namespace Webkul\\Admin\\...` → `namespace Medsdn\\Admin\\...`
- `use Webkul\\...` → `use Medsdn\\...`
- string references in config/views from `Webkul\...` to `Medsdn\...`

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Unit/Project/IdentifierCodemodAcceptanceTest.php --filter=write`

Expected: FAIL because dry-run exists but does not write transformations.

**Step 3: Write minimal implementation**

Extend the script so that `--write`:

- rewrites target files
- preserves untouched files byte-for-byte
- updates `composer.json` and `packages/medsdn/Admin/composer.json`
- reports file-level changes

**Step 4: Run test to verify it passes**

Run: `php artisan test tests/Unit/Project/IdentifierCodemodAcceptanceTest.php --filter=write`

Expected: PASS

**Step 5: Commit**

```bash
git add scripts/rebrand-identifiers.php composer.json packages/medsdn/Admin/composer.json tests/Unit/Project/IdentifierCodemodAcceptanceTest.php
git commit -m "feat: implement write-mode identifier rebrand codemod"
```

### Task 5: Execute Codemod On Admin Scope

**Files:**
- Modify: `packages/medsdn/Admin/src/Config/system.php`
- Modify: `packages/medsdn/Admin/src/Config/menu.php`
- Modify: `packages/medsdn/Admin/src/Config/acl.php`
- Modify: `packages/medsdn/Admin/src/Resources/views/**/*.blade.php`
- Modify: `packages/medsdn/Admin/src/**/*.php`

**Step 1: Run the script in dry-run**

Run: `php scripts/rebrand-identifiers.php --scope=admin --dry-run`

Expected: A non-empty report listing `bagisto_asset` and `Webkul` replacements.

**Step 2: Run the script in write mode**

Run: `php scripts/rebrand-identifiers.php --scope=admin --write`

Expected: Files rewritten under `packages/medsdn/Admin`.

**Step 3: Review the diff**

Run: `git diff -- packages/medsdn/Admin composer.json packages/medsdn/Admin/composer.json`

Expected: Only the intended identifier changes.

**Step 4: Run focused searches**

Run: `rg -n "bagisto_asset\\(|namespace Webkul\\\\|use Webkul\\\\|Webkul\\\\[A-Za-z]" packages/medsdn/Admin`

Expected: Empty or limited to explicitly documented exclusions.

**Step 5: Commit**

```bash
git add packages/medsdn/Admin composer.json packages/medsdn/Admin/composer.json
git commit -m "refactor: rebrand admin identifiers to medsdn"
```

### Task 6: Rebuild Autoload And Verify Runtime

**Files:**
- Modify: `tests/Unit/Project/PackagePathBrandingTest.php`
- Modify: `packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php`
- Test: `tests/Unit/Project/IdentifierCodemodAcceptanceTest.php`

**Step 1: Run autoload rebuild**

Run: `composer dump-autoload`

Expected: Composer completes without PSR-4 resolution errors.

**Step 2: Run targeted tests**

Run: `php artisan test tests/Unit/Project/PackagePathBrandingTest.php tests/Unit/Project/IdentifierCodemodAcceptanceTest.php packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php`

Expected: PASS

**Step 3: Run final residue search**

Run: `rg -n "bagisto_asset\\(|namespace Webkul\\\\|use Webkul\\\\|Webkul\\\\[A-Za-z]" packages/medsdn/Admin composer.json packages/medsdn/Admin/composer.json`

Expected: Empty or only deliberate compatibility leftovers documented in the diff.

**Step 4: Document remaining compatibility**

If any `bagisto_*` event names or package names remain, add a short note to:

- `docs/plans/2026-03-07-identifier-rebrand-design.md`

**Step 5: Commit**

```bash
git add tests/Unit/Project/PackagePathBrandingTest.php tests/Unit/Project/IdentifierCodemodAcceptanceTest.php packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php docs/plans/2026-03-07-identifier-rebrand-design.md
git commit -m "test: verify admin identifier rebrand rollout"
```
