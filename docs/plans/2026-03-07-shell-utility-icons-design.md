# Shell Utility Icons Design

**Date:** 2026-03-07

## Goal

Replace the `dark-mode` and `notifications` header utility glyphs with new Streamline-based shell icons while preserving the existing interaction logic.

## Scope

This design covers only the top header utility buttons:

- dark mode toggle
- notifications bell trigger

It does not change:

- order status icons inside the notifications dropdown
- sidebar icons
- quick creation icons

## Chosen Approach

Extend the existing shell icon system with utility-specific state names:

- `dark-mode-light`
- `dark-mode-dark`
- `notifications`
- `notifications-unread`

These icons will be stored with the rest of the shell SVG assets and rendered through `x-admin::icon.shell`.

## Why This Approach

- keeps the new utility buttons visually aligned with the existing shell icon migration
- avoids mixing new colored assets with legacy font classes in the same header cluster
- preserves current Vue and Blade behavior by swapping only the visual representation

## Interaction Rules

- Dark mode still switches based on the `dark_mode` cookie and client-side `isDarkMode` state.
- Notifications still use the same dropdown component and unread count badge.
- The unread bell gets a separate icon name so the visual state can change without touching notification business logic.

## Testing

- update header shell tests to expect the new utility icon markers and asset paths
- keep notification status icon tests unchanged
- rebuild Admin assets after adding the new SVG files
