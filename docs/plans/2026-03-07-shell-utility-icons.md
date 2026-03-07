# Shell Utility Icons Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Replace the header `dark-mode` and `notifications` glyphs with Streamline-based shell icons while keeping their existing behavior intact.

**Architecture:** Add four utility SVG assets to the shell icon library, then update the header Blade templates to render those icons through `x-admin::icon.shell` for both default and active states. Keep order-status icons inside the notifications dropdown on the current font system.

**Tech Stack:** Laravel Blade, package assets, Admin Vite build, PHPUnit feature tests

---

### Task 1: Define the new utility icon expectations

**Files:**
- Modify: `packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php`
- Modify: `docs/plans/2026-03-07-shell-utility-icons-design.md`

**Step 1: Write the failing test**

Add header assertions for:

- `data-shell-icon="dark-mode-light"`
- `data-shell-icon="dark-mode-dark"`
- `data-shell-icon="notifications"`
- `data-shell-icon="notifications-unread"`

**Step 2: Run test to verify it fails**

Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php --filter='header|notifications'`
Expected: FAIL before assertions complete in the current environment, but the new expectations are present and not yet implemented in Blade.

**Step 3: Write minimal implementation**

Document the exact utility icon names and their limited scope in the design doc.

**Step 4: Run test to verify it passes**

Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php --filter='header|notifications'`
Expected: still blocked or FAIL until production changes land.

**Step 5: Commit**

```bash
git add packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php docs/plans/2026-03-07-shell-utility-icons-design.md
git commit -m "test: define shell utility icon expectations"
```

### Task 2: Add Streamline utility icon assets

**Files:**
- Create: `packages/medsdn/Admin/src/Resources/assets/images/streamline-plump-color/dark-mode-light.svg`
- Create: `packages/medsdn/Admin/src/Resources/assets/images/streamline-plump-color/dark-mode-dark.svg`
- Create: `packages/medsdn/Admin/src/Resources/assets/images/streamline-plump-color/notifications.svg`
- Create: `packages/medsdn/Admin/src/Resources/assets/images/streamline-plump-color/notifications-unread.svg`
- Modify: `packages/medsdn/Admin/docs/streamline-shell-icons-attribution.md`

**Step 1: Write the failing test**

Ensure the shell asset existence test includes the four new utility SVG files.

**Step 2: Run test to verify it fails**

Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php --filter='streamline shell icon source files'`
Expected: FAIL before assertions complete in the current environment, and the new asset files are not yet present.

**Step 3: Write minimal implementation**

Download or add the four utility SVG files from the approved Streamline set and note them in the attribution doc.

**Step 4: Run test to verify it passes**

Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php --filter='streamline shell icon source files'`
Expected: still environment-blocked or PASS once the environment allows the test to run.

**Step 5: Commit**

```bash
git add packages/medsdn/Admin/src/Resources/assets/images/streamline-plump-color packages/medsdn/Admin/docs/streamline-shell-icons-attribution.md
git commit -m "chore: add shell utility icon assets"
```

### Task 3: Switch the header utility buttons to shell icons

**Files:**
- Modify: `packages/medsdn/Admin/src/Resources/views/components/layouts/header/index.blade.php`
- Modify: `packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php`

**Step 1: Write the failing test**

Extend header assertions so the default and unread notification triggers and the dark mode toggle render the new shell icon hooks.

**Step 2: Run test to verify it fails**

Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php --filter='header|notifications'`
Expected: FAIL because the header still uses `icon-light`, `icon-dark`, and `icon-notification`.

**Step 3: Write minimal implementation**

Replace the font-based utility glyphs in the header with `x-admin::icon.shell`, keyed by the existing state logic.

**Step 4: Run test to verify it passes**

Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php --filter='header|notifications'`
Expected: still blocked or PASS once the environment allows the test to execute.

**Step 5: Commit**

```bash
git add packages/medsdn/Admin/src/Resources/views/components/layouts/header/index.blade.php packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php
git commit -m "feat: switch shell utility buttons to streamline icons"
```

### Task 4: Rebuild and verify the Admin package

**Files:**
- Inspect: `public/themes/admin/default/build`
- Inspect: `packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php`

**Step 1: Write the failing test**

No new test file is required beyond the header regression coverage.

**Step 2: Run test to verify it fails**

Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php --filter='header|notifications'`
Expected: FAIL or environment-blocked until the header implementation is complete.

**Step 3: Write minimal implementation**

Make any final asset or Blade adjustments needed for the new utility icons, then rebuild the Admin assets.

**Step 4: Run test to verify it passes**

Run:

```bash
php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php --filter='header|notifications'
cd packages/medsdn/Admin && npm run build
```

Expected:

- header tests PASS when the local PHP environment is healthy
- Admin asset build completes successfully

**Step 5: Commit**

```bash
git add packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php public/themes/admin/default/build
git commit -m "feat: add streamline header utility icons"
```
