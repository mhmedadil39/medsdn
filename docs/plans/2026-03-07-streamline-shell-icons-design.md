# Streamline Shell Icons Design

**Date:** 2026-03-07

## Goal

Adopt Streamline Plump Color icons in the MedSDN Admin shell without breaking the existing `icomoon`-based icon system used throughout the wider admin application.

## Scope

This design applies only to the Admin shell surfaces:

- top header
- desktop sidebar
- mobile sidebar
- quick creation dropdown

The rest of Admin continues to use the current `icomoon` font until a later migration phase.

## Constraints

- The requested icon set is multicolor, so a classic monochrome icon font is the wrong storage format for the new shell icons.
- The current Admin UI already depends on many `.icon-*` classes across dozens of views, so replacing the entire font system in one pass would create unnecessary regression risk.
- The selected collection requires attribution under its published license terms.
- Existing small functional icons such as arrows, toggles, checkboxes, notifications, and datagrid controls must remain stable.

## Chosen Approach

Use a hybrid icon system:

- keep `icomoon.woff` for existing utility and operational icons
- add a local Streamline SVG library for the new shell icons
- expose Streamline icons through a reusable Blade component so views do not hardcode raw `<img>` or SVG file paths
- migrate only shell-facing icon placements to the new component

## Why This Approach

This preserves the visual quality of the multicolor Streamline set, avoids forcing color icons into a fragile font pipeline, and limits the blast radius to the areas the user explicitly prioritized.

## Architecture

### 1. Local asset library

Create a dedicated Admin asset area for downloaded Streamline SVG files. The source-of-truth icons stay as SVG files in the package so they are versioned, readable, and reusable.

### 2. Shared shell icon component

Add a Blade component dedicated to shell icons. The component resolves named icons to packaged SVG assets, applies consistent sizing classes, and provides safe fallbacks when an icon name is missing.

### 3. Shell-only migration

Replace the shell's current icon usages with the new component only in:

- header brand-adjacent actions where a colored icon is appropriate
- sidebar primary navigation icons
- quick creation menu cards

Utility glyphs that are fundamentally typographic or stateful remain on the font system.

### 4. Attribution

Add a small attribution file in the package or repository docs so the imported Streamline assets are redistributed correctly.

## Mapping Rules

- Primary navigation destinations get one unique Streamline icon each.
- Quick creation actions get one unique Streamline icon each.
- Theme toggle, chevrons, sorting, notification badges, checkbox states, and similar UI mechanics remain on the font system unless a later phase intentionally replaces them.
- Existing `settings/*.svg` placeholders already in use can be retired or re-pointed where the new Streamline assets replace them.

## Shell Icon Inventory

### Header

- `visit-shop` -> `streamline-plump-color/visit-shop.svg`
- `dark-mode`, `notifications`, and dropdown carets stay on the font system for now
- the brand/logo block gets a stable test hook via `data-role="shell-brand-lockup"`

### Quick Creation

- `quick-product` -> `streamline-plump-color/quick-product.svg`
- `quick-category` -> `streamline-plump-color/quick-category.svg`
- `quick-attribute` -> `streamline-plump-color/quick-attribute.svg`
- `quick-family` -> `streamline-plump-color/quick-family.svg`
- `quick-cms` -> `streamline-plump-color/quick-cms.svg`
- `quick-cart-rule` -> `streamline-plump-color/quick-cart-rule.svg`
- `quick-inventory-source` -> `streamline-plump-color/quick-inventory-source.svg`
- `quick-role` -> `streamline-plump-color/quick-role.svg`
- the quick-create grid gets a stable test hook via `data-role="header-quick-create-grid"`

### Desktop Sidebar

- `sidebar-dashboard` -> `streamline-plump-color/sidebar-dashboard.svg`
- `sidebar-sales` -> `streamline-plump-color/sidebar-sales.svg`
- `sidebar-catalog` -> `streamline-plump-color/sidebar-catalog.svg`
- `sidebar-customers` -> `streamline-plump-color/sidebar-customers.svg`
- `sidebar-cms` -> `streamline-plump-color/sidebar-cms.svg`
- `sidebar-marketing` -> `streamline-plump-color/sidebar-marketing.svg`
- `sidebar-reporting` -> `streamline-plump-color/sidebar-reporting.svg`
- `sidebar-settings` -> `streamline-plump-color/sidebar-settings.svg`
- `sidebar-configuration` -> `streamline-plump-color/sidebar-configuration.svg`

### Mobile Sidebar

- mobile drawer uses the same semantic icon names as desktop
- mobile render hooks use `data-mobile-shell-icon="<name>"`
- accordion chevrons stay on the font system

## Testing Strategy

- Add render tests that verify shell views use the new shell icon component or expected new asset paths.
- Keep focused tests for the header, desktop sidebar, mobile sidebar, and quick creation dropdown.
- Build Admin assets after the view and style changes to ensure no bundling regressions.

## Out of Scope

- Replacing every `.icon-*` usage in Admin
- Rebuilding `icomoon.woff`
- Converting the full Streamline collection into a font file
- Changing unrelated package icon usage outside the Admin shell
