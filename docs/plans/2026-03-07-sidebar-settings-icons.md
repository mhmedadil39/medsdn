# Sidebar Settings Icons Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Render settings-directory SVG icons for top-level admin sidebar items in desktop and mobile layouts.

**Architecture:** The implementation stays inside the sidebar view layer. A small key-to-asset map will be added to each sidebar Blade file so the current menu contract remains unchanged. Tests will assert that sidebar markup renders image assets for top-level items instead of font icon classes.

**Tech Stack:** Laravel Blade, Pest, MedSDN Vite asset helper

---

### Task 1: Add the failing sidebar icon test

**Files:**
- Modify: `packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php`

**Step 1: Write the failing test**
- Extend the sidebar shell assertions so they expect image-based settings assets for top-level sidebar items.

**Step 2: Run test to verify it fails**
- Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php --filter="sidebar"`
- Expected: FAIL because the sidebar still renders font icon spans.

### Task 2: Implement desktop sidebar settings icons

**Files:**
- Modify: `packages/medsdn/Admin/src/Resources/views/components/layouts/sidebar/desktop/index.blade.php`

**Step 1: Write minimal implementation**
- Add a top-level icon map keyed by menu item key.
- Replace the current top-level font icon span with an image tag that uses the mapped settings asset.
- Preserve the arrow icon and all active/hover/collapse logic.

**Step 2: Run targeted test**
- Run the sidebar layout test again.

### Task 3: Implement mobile sidebar settings icons

**Files:**
- Modify: `packages/medsdn/Admin/src/Resources/views/components/layouts/sidebar/mobile/index.blade.php`

**Step 1: Write minimal implementation**
- Reuse the same key-to-asset mapping idea for the mobile drawer.
- Replace the top-level font icon span with an image tag.
- Preserve accordion behavior and active state classes.

**Step 2: Run targeted test**
- Run the sidebar layout test again.

### Task 4: Verify the full shell suite

**Files:**
- Test: `packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php`

**Step 1: Run full shell test file**
- Run: `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php`
- Expected: PASS

**Step 2: Rebuild admin assets if needed**
- Run: `npm run build`
- Expected: PASS
