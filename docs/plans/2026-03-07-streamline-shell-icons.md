# Streamline Shell Icons Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Integrate Streamline Plump Color icons into the MedSDN Admin shell through a hybrid SVG + font approach, keeping the existing icon font for non-shell UI.

**Architecture:** Store Streamline icons as packaged SVG assets, expose them through a shared shell icon Blade component, and migrate only the header, sidebars, and quick creation menu to the new component. Keep `icomoon` in place for operational glyphs and interaction-heavy states to minimize regressions.

**Tech Stack:** Laravel Blade, package assets, Admin Vite build, PHPUnit feature tests

---

### Task 1: Inventory shell icon touchpoints and target mappings

**Files:**
- Modify: `docs/plans/2026-03-07-streamline-shell-icons-design.md`
- Inspect: `packages/medsdn/Admin/src/Resources/views/components/layouts/header/index.blade.php`
- Inspect: `packages/medsdn/Admin/src/Resources/views/components/layouts/header/quick-creation.blade.php`
- Inspect: `packages/medsdn/Admin/src/Resources/views/components/layouts/sidebar/desktop/index.blade.php`
- Inspect: `packages/medsdn/Admin/src/Resources/views/components/layouts/sidebar/mobile/index.blade.php`

**Step 1: Write the failing test**

Add or update a focused test skeleton in the Admin layout test suite that expects shell icon hooks to exist for header, sidebar, and quick-create items.

**Step 2: Run test to verify it fails**

Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php --filter=icon`
Expected: FAIL because the new shell icon hooks are not implemented yet.

**Step 3: Write minimal implementation**

Document the final icon name mapping in the design doc and confirm the exact shell placements that will migrate.

**Step 4: Run test to verify it passes**

Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php --filter=icon`
Expected: still FAIL until the component and views are added; this task establishes the target inventory only.

**Step 5: Commit**

```bash
git add docs/plans/2026-03-07-streamline-shell-icons-design.md packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php
git commit -m "test: define shell icon migration targets"
```

### Task 2: Add Streamline asset source and attribution

**Files:**
- Create: `packages/medsdn/Admin/src/Resources/assets/images/streamline-plump-color/`
- Create: `packages/medsdn/Admin/src/Resources/assets/images/streamline-plump-color/ATTRIBUTION.md`
- Modify: `packages/medsdn/Admin/src/Resources/assets/images/streamline-plump-color/README.md`

**Step 1: Write the failing test**

Add a source-level test that checks required shell icon asset files exist for the mapped names.

**Step 2: Run test to verify it fails**

Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php --filter=streamline`
Expected: FAIL because the Streamline asset files do not exist yet.

**Step 3: Write minimal implementation**

Download the approved Streamline SVG files into the new asset folder and add attribution documentation describing the icon source and license obligations.

**Step 4: Run test to verify it passes**

Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php --filter=streamline`
Expected: PASS for the asset existence assertions.

**Step 5: Commit**

```bash
git add packages/medsdn/Admin/src/Resources/assets/images/streamline-plump-color
git commit -m "chore: add streamline shell icon assets"
```

### Task 3: Build a reusable shell icon Blade component

**Files:**
- Create: `packages/medsdn/Admin/src/Resources/views/components/icon/shell.blade.php`
- Modify: `packages/medsdn/Admin/src/Resources/views/components/layouts/index.blade.php`
- Test: `packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php`

**Step 1: Write the failing test**

Extend the shell layout test to assert the new icon component renders deterministic `data-shell-icon` markers and asset paths for named icons.

**Step 2: Run test to verify it fails**

Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php --filter=shell`
Expected: FAIL because no reusable shell icon component exists yet.

**Step 3: Write minimal implementation**

Create a Blade component that:

- accepts a required icon name
- resolves that name to a packaged SVG asset
- applies standard sizing classes
- renders a safe fallback icon when a mapping is missing
- exposes a `data-shell-icon` attribute for testing

**Step 4: Run test to verify it passes**

Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php --filter=shell`
Expected: PASS for component rendering assertions.

**Step 5: Commit**

```bash
git add packages/medsdn/Admin/src/Resources/views/components/icon/shell.blade.php packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php
git commit -m "feat: add reusable admin shell icon component"
```

### Task 4: Migrate header and quick creation icons

**Files:**
- Modify: `packages/medsdn/Admin/src/Resources/views/components/layouts/header/index.blade.php`
- Modify: `packages/medsdn/Admin/src/Resources/views/components/layouts/header/quick-creation.blade.php`
- Test: `packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php`

**Step 1: Write the failing test**

Add assertions that quick creation cards and shell-level colored placements in the header use the new shell icon component or new Streamline asset hooks.

**Step 2: Run test to verify it fails**

Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php --filter=quick`
Expected: FAIL because quick creation still points at the previous asset set.

**Step 3: Write minimal implementation**

Replace the quick creation icon images with the new shell icon component and update any header placements that should display Streamline shell icons.

**Step 4: Run test to verify it passes**

Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php --filter=quick`
Expected: PASS for quick creation and header shell icon assertions.

**Step 5: Commit**

```bash
git add packages/medsdn/Admin/src/Resources/views/components/layouts/header/index.blade.php packages/medsdn/Admin/src/Resources/views/components/layouts/header/quick-creation.blade.php packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php
git commit -m "feat: migrate header shell icons to streamline assets"
```

### Task 5: Migrate desktop and mobile sidebar icons

**Files:**
- Modify: `packages/medsdn/Admin/src/Resources/views/components/layouts/sidebar/desktop/index.blade.php`
- Modify: `packages/medsdn/Admin/src/Resources/views/components/layouts/sidebar/mobile/index.blade.php`
- Test: `packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php`

**Step 1: Write the failing test**

Add assertions that each primary sidebar item renders a unique shell icon mapping on both desktop and mobile variants.

**Step 2: Run test to verify it fails**

Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php --filter=sidebar`
Expected: FAIL because the sidebar still relies on the previous icon path map.

**Step 3: Write minimal implementation**

Replace the sidebar image path map with the new shell icon component and keep active-state styling compatible with light and dark modes.

**Step 4: Run test to verify it passes**

Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php --filter=sidebar`
Expected: PASS for both desktop and mobile shell icon assertions.

**Step 5: Commit**

```bash
git add packages/medsdn/Admin/src/Resources/views/components/layouts/sidebar/desktop/index.blade.php packages/medsdn/Admin/src/Resources/views/components/layouts/sidebar/mobile/index.blade.php packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php
git commit -m "feat: migrate sidebar shell icons to streamline assets"
```

### Task 6: Build and verify the Admin package

**Files:**
- Inspect: `packages/medsdn/Admin/src/Resources/assets/css/app.css`
- Inspect: `packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php`

**Step 1: Write the failing test**

No new test file is required beyond the previous shell regression coverage.

**Step 2: Run test to verify it fails**

Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php`
Expected: FAIL until all shell migrations are complete.

**Step 3: Write minimal implementation**

Make the final CSS adjustments needed for image sizing, spacing, or dark-mode treatment, then rebuild Admin assets.

**Step 4: Run test to verify it passes**

Run:

```bash
php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php
cd packages/medsdn/Admin && npm run build
```

Expected:

- layout shell tests PASS
- Admin asset build completes successfully

**Step 5: Commit**

```bash
git add packages/medsdn/Admin/src/Resources/assets/css/app.css packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php public/themes/admin/default/build
git commit -m "feat: adopt streamline icons for admin shell"
```
