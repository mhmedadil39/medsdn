# Sidebar Settings Icons Design

**Goal:** Replace admin sidebar top-level font icons with image assets from `packages/medsdn/Admin/src/Resources/assets/images/settings` for both desktop and mobile layouts.

**Scope:**
- Top-level sidebar items only
- Desktop sidebar
- Mobile sidebar drawer
- No submenu icon work

**Approved Approach:**
- Keep `menu.php` unchanged
- Add a view-local image map keyed by menu item key in sidebar Blade components
- Render `<img>` for the mapped top-level item icon instead of the current icon font span
- Preserve existing active, hover, collapsed, and dark-mode behaviors

**Approved Mapping:**
- `dashboard` -> `store.svg`
- `sales` -> `order.svg`
- `catalog` -> `product.svg`
- `customers` -> `users.svg`
- `cms` -> `notes.svg`
- `marketing` -> `quotes.svg`
- `reporting` -> `activities.svg`
- `settings` -> `settings.svg`
- `configuration` -> `theme.svg`

**Constraints:**
- Use assets from the `settings` directory only
- Keep submenu markup and interaction unchanged
- Keep collapse/hover shell logic unchanged
- Use TDD before implementation
