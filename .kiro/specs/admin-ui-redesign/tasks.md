# Implementation Plan: Admin UI Redesign

## Overview

This implementation plan breaks down the Admin UI Redesign into discrete, actionable coding tasks. The redesign modernizes the MedSDN Admin Panel with an enhanced sidebar navigation system, improved hover interactions, responsive mobile drawer, and cohesive design system using Tailwind CSS and Vue.js.

The implementation follows a bottom-up approach: starting with configuration and utilities, then building core components, followed by integration, testing, and documentation.

## Tasks

- [x] 1. Setup and Configuration
  - [x] 1.1 Update Tailwind configuration with new colors and settings
    - Update `packages/Webkul/Admin/tailwind.config.js`
    - Add `brandColor` with full color palette (50-900)
    - Configure dark mode strategy as 'class'
    - Add icon font family to fontFamily extend
    - Safelist icon classes with pattern `/icon-/`
    - Safelist brand color variations
    - Add custom z-index values (10001, 10002, 10003)
    - Add transition duration 80ms
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 10.6_

  - [x] 1.2 Update CSS utilities and custom styles
    - Update `packages/Webkul/Admin/src/Resources/assets/css/app.css`
    - Add icon font-face definitions for icomoon
    - Create `.sidebar-rounded` utility class
    - Create `.journal-scroll` custom scrollbar styling with dark mode support
    - Update button classes (primary-button, secondary-button, transparent-button) to use brandColor
    - Add `.box-shadow` utility with dark mode variant
    - Maintain `.shimmer` animation for loading states
    - Add GPU acceleration styles for sidebar and submenu
    - _Requirements: 15.1, 15.2, 15.3, 15.4, 15.5, 15.6, 7.1_

  - [x] 1.3 Git commit for setup and configuration
    - Stage all changes: `git add packages/Webkul/Admin/tailwind.config.js packages/Webkul/Admin/src/Resources/assets/css/app.css`
    - Commit: `git commit -m "feat(admin): update Tailwind config and CSS utilities for new UI design"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "feat(admin): Setup Tailwind config and CSS utilities" --body "Part 1: Initial configuration for admin UI redesign"`


- [-] 2. Vue.js State Management and Root Component
  - [x] 2.1 Update Vue.js root component with new state management
    - Update `packages/Webkul/Admin/src/Resources/assets/js/app.js`
    - Add `isMenuActive` state to track clicked menu
    - Add `hoveringMenu` state to track hovered menu
    - Add `sidebarCollapsed` state with localStorage persistence
    - Add `activeMenu` state for mobile drawer
    - Add `mobileDrawerOpen` state
    - _Requirements: 9.1, 9.2_

  - [x] 2.2 Implement sidebar interaction methods
    - Implement `handleMouseOver(menuKey)` method for hover expansion
    - Implement `handleMouseLeave()` method for hover collapse
    - Implement `handleFocusOut(event)` method for click-outside detection
    - Implement `toggleSidebar()` method with localStorage sync
    - Implement `shouldShowSubmenu(menuKey)` helper method
    - Add event listener cleanup in beforeUnmount hook
    - _Requirements: 9.3, 9.4, 9.5, 9.7, 2.4_

  - [x] 2.3 Implement mobile drawer methods
    - Implement `toggleMobileMenu(menuKey)` method for accordion behavior
    - Implement `autoExpandActiveMenu()` method to expand active menu on mount
    - Call `autoExpandActiveMenu()` in mounted hook
    - _Requirements: 9.8, 9.9, 5.10_

  - [ ] 2.4 Write property test for Vue state management
    - **Property 1: Sidebar collapse state round-trip**
    - **Validates: Requirements 1.2, 1.3, 17.12**
    - Test that toggling sidebar twice returns to original state
    - Use Pest with property-based testing plugin
    - Run 100 iterations with random initial states

  - [ ] 2.5 Write property test for hover behavior with active state override
    - **Property 2: Hover expansion with active state override**
    - **Validates: Requirements 2.1, 2.2, 2.3**
    - Test hover expands sidebar and mouse leave collapses it
    - Test that isMenuActive=true keeps sidebar expanded regardless of hover
    - Run 100 iterations

  - [x] 2.6 Git commit for Vue.js state management
    - Stage changes: `git add packages/Webkul/Admin/src/Resources/assets/js/app.js`
    - Commit: `git commit -m "feat(admin): implement Vue.js state management for sidebar interactions"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "feat(admin): Vue.js state management for sidebar" --body "Part 2: Implement reactive state management for sidebar interactions and mobile drawer"`


- [-] 3. Helper Functions and Utilities
  - [x] 3.1 Implement menu name resolution helper
    - Create `resolveMenuName($menuItem)` function in helper file
    - Check for custom name in `general.settings.menu.{menuKey}` config
    - Return custom name if exists, otherwise return default name
    - Add error handling with logging and fallback to default
    - _Requirements: 12.1, 12.2, 12.3_

  - [x] 3.2 Implement ACL permission filter helper
    - Create `filterMenuByPermissions($menuItems)` function
    - Filter menu items based on user permissions using bouncer
    - Recursively filter children menus
    - Remove parent if all children are filtered out
    - Add error handling with fail-secure approach (hide on error)
    - _Requirements: 14.2, 14.3, 14.4_

  - [x] 3.3 Implement responsive breakpoint detection helpers
    - Create `getCurrentBreakpoint()` function in JavaScript
    - Create `isMobileViewport()` function to check if below lg breakpoint
    - _Requirements: 8.7_

  - [ ] 3.4 Write property test for custom menu name resolution
    - **Property 20: Custom menu name resolution**
    - **Validates: Requirements 12.1, 12.2, 12.3, 12.4**
    - Test that custom names override defaults
    - Test with random menu items and optional custom names
    - Run 100 iterations

  - [ ] 3.5 Write property test for ACL permission filtering
    - **Property 27: ACL permission filtering**
    - **Validates: Requirements 14.2, 14.3**
    - Test that menu items without permission are filtered out
    - Test with random menu items and permission sets
    - Run 100 iterations

  - [x] 3.6 Git commit for helper functions
    - Stage changes: `git add packages/Webkul/Admin/src/Http/helpers.php`
    - Commit: `git commit -m "feat(admin): add helper functions for menu name resolution and ACL filtering"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "feat(admin): Helper functions for menu customization" --body "Part 3: Add utility functions for custom menu names and ACL permission filtering"`


- [-] 4. Desktop Sidebar Component
  - [x] 4.1 Update desktop sidebar structure and styling
    - Update `packages/Webkul/Admin/src/Resources/views/components/layouts/sidebar/desktop/index.blade.php`
    - Set fixed positioning: `top-[60px]`, `h-[calc(100vh-60px)]`
    - Set width: `w-[200px]` expanded, `group-[.sidebar-collapsed]/container:w-[70px]` collapsed
    - Add background colors with dark mode support
    - Add border-r with dark mode variant
    - Add smooth transitions: `transition-all duration-300`
    - Add z-index: `z-[10002]`
    - Add custom scrollbar: `journal-scroll`
    - Hide on mobile: `max-lg:hidden`
    - _Requirements: 1.1, 1.2, 1.6, 1.7, 1.8, 8.1_

  - [x] 4.2 Implement menu item rendering with custom names
    - Loop through menu items from `menu()->getItems('admin')`
    - Check ACL permissions using `bouncer()->hasPermission()`
    - Resolve custom menu names using `resolveMenuName()` helper
    - Detect active state using `request()->is()`
    - Detect if menu has children
    - _Requirements: 12.1, 12.2, 14.1, 14.2_

  - [x] 4.3 Implement menu item links with proper styling
    - Create anchor tags with proper href
    - Add data-menu-key attribute for JavaScript targeting
    - Apply active state styling: `bg-brandColor text-white`
    - Apply inactive state styling: `text-gray-600 dark:text-gray-300`
    - Add hover states: `hover:bg-gray-100 dark:hover:bg-gray-950`
    - Add smooth color transitions: `transition-colors duration-200`
    - Add ARIA labels for accessibility
    - _Requirements: 4.3, 4.4, 4.5, 13.1, 13.2_

  - [x] 4.4 Implement menu item icons and text labels
    - Render icon with `icon-{menuItem.getIcon()}` class
    - Set icon size: `text-xl`
    - Add icon title attribute for tooltips
    - Render text label with flex-1
    - Hide text in collapsed state: `group-[.sidebar-collapsed]/container:hidden`
    - _Requirements: 1.3, 7.1, 7.2, 7.3, 13.7_

  - [x] 4.5 Implement arrow indicators for parent menus
    - Show arrow only if menu has children
    - Use `icon-right-arrow` for LTR, `icon-left-arrow` for RTL
    - Hide arrow in collapsed state
    - Set arrow size: `text-sm`
    - _Requirements: 3.1_

  - [x] 4.6 Implement hover event handlers
    - Add @mouseover event to trigger `handleMouseOver(menuKey)` for parent menus
    - Add @click.prevent event to toggle `isMenuActive` for parent menus
    - Add @mouseover event to sidebar nav element
    - Add @mouseleave event to sidebar nav element
    - _Requirements: 2.1, 2.2, 3.6_

  - [ ] 4.7 Write property test for menu items with children display arrow
    - **Property 3: Menu items with children display arrow indicator**
    - **Validates: Requirements 3.1**
    - Test that all menu items with children show arrow icon
    - Test with random menu items
    - Run 100 iterations

  - [ ] 4.8 Write property test for active menu item color application
    - **Property 8: Active menu item color application**
    - **Validates: Requirements 4.3, 4.4**
    - Test active items use brandColor, inactive use gray
    - Test with random menu items and active states
    - Run 100 iterations

  - [ ] 4.9 Write unit tests for desktop sidebar rendering
    - Test sidebar renders with correct initial state
    - Test collapsed sidebar hides menu text
    - Test menu with no children does not show arrow
    - Test active menu item has correct classes

  - [x] 4.10 Git commit for desktop sidebar component
    - Stage changes: `git add packages/Webkul/Admin/src/Resources/views/components/layouts/sidebar/desktop/index.blade.php`
    - Commit: `git commit -m "feat(admin): implement desktop sidebar with hover interactions and submenu support"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "feat(admin): Desktop sidebar with hover interactions" --body "Part 4: Complete desktop sidebar implementation with expand/collapse, hover behavior, and custom menu names"`


- [ ] 5. Submenu Panel Component
  - [x] 5.1 Implement submenu panel structure
    - Add submenu div inside parent menu item li element
    - Set absolute positioning: `ltr:left-[200px] rtl:right-[199px]`
    - Set width: `min-w-[140px] max-w-max`
    - Add background with dark mode: `bg-white dark:bg-gray-900`
    - Add border with dark mode: `border border-gray-200 dark:border-gray-800`
    - Add rounded corners: `rounded-lg`
    - Add shadow: `shadow-lg`
    - Add z-index: `z-[10003]`
    - _Requirements: 3.2, 3.3, 3.4, 3.5_

  - [x] 5.2 Implement submenu visibility control
    - Use v-show directive with `shouldShowSubmenu(menuKey)` condition
    - Add data-submenu attribute for testing
    - Add @mouseover.stop event to prevent event bubbling
    - Add smooth transitions: `transition-all duration-300`
    - _Requirements: 3.7, 3.8_

  - [x] 5.3 Implement submenu item rendering
    - Loop through child menu items
    - Check ACL permissions for each child
    - Resolve custom names for children
    - Detect active state for children
    - Render anchor tags with proper styling
    - _Requirements: 12.4, 14.2_

  - [x] 5.4 Style submenu items
    - Apply same color scheme as parent menu
    - Active: `bg-brandColor text-white`
    - Inactive: `text-gray-600 dark:text-gray-300`
    - Hover: `hover:bg-gray-100 dark:hover:bg-gray-950`
    - Add icon with `text-lg` size
    - Add smooth transitions
    - _Requirements: 4.3, 4.4, 4.5_

  - [ ] 5.5 Write property test for parent menu hover shows submenu
    - **Property 4: Parent menu hover shows submenu panel**
    - **Validates: Requirements 3.2**
    - Test hovering parent menu displays submenu
    - Test positioning (right for LTR, left for RTL)
    - Run 100 iterations

  - [ ] 5.6 Write property test for submenu styling consistency
    - **Property 5: Submenu styling consistency**
    - **Validates: Requirements 3.5**
    - Test submenu uses same styling as main sidebar
    - Test dark mode support
    - Run 100 iterations

  - [ ] 5.7 Write property test for menu click toggles active state
    - **Property 6: Menu click toggles active state**
    - **Validates: Requirements 3.6, 3.7**
    - Test clicking menu toggles isMenuActive
    - Test submenu remains visible when active
    - Run 100 iterations

  - [ ] 5.8 Write property test for submenu hides on mouse leave
    - **Property 7: Submenu hides on mouse leave**
    - **Validates: Requirements 3.8**
    - Test submenu hides when mouse leaves both parent and submenu
    - Run 100 iterations

  - [x] 5.9 Git commit for submenu panel component
    - Stage changes: `git add packages/Webkul/Admin/src/Resources/views/components/layouts/sidebar/desktop/index.blade.php`
    - Commit: `git commit -m "feat(admin): implement submenu panel with positioning and visibility control"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "feat(admin): Submenu panel with RTL support" --body "Part 5: Add submenu panel with proper positioning, visibility control, and RTL layout support"`


- [ ] 6. Mobile Drawer Component
  - [x] 6.1 Update mobile drawer structure
    - Update `packages/Webkul/Admin/src/Resources/views/components/layouts/sidebar/mobile/index.blade.php`
    - Use x-admin::drawer component with left position
    - Set width: `280px` with responsive override `[&>:nth-child(3)]:max-sm:!w-[80%]`
    - _Requirements: 5.1, 5.2_

  - [x] 6.2 Implement hamburger menu toggle button
    - Create button in drawer toggle slot
    - Add data-testid="hamburger-menu" for testing
    - Use `icon-menu` with `text-2xl` size
    - Apply hover states and transitions
    - Add ARIA label for accessibility
    - _Requirements: 6.4, 13.2_

  - [x] 6.3 Implement drawer header
    - Add header with menu title
    - Use translation: `__('admin::app.layouts.menu')`
    - Add border-b separator with dark mode support
    - _Requirements: 6.1_

  - [x] 6.4 Implement mobile menu items with accordion behavior
    - Loop through menu items with ACL filtering
    - Resolve custom menu names
    - Detect active states
    - For parent menus: create button with @click="toggleMobileMenu(menuKey)"
    - For simple menus: create anchor tag
    - Add data-mobile-menu attributes for testing
    - _Requirements: 5.3, 9.8, 12.1_

  - [x] 6.5 Implement mobile submenu expansion
    - Add arrow icon with rotation animation based on activeMenu state
    - Use `icon-arrow-down` with conditional rotation class
    - Apply transition: `transition-transform duration-300`
    - _Requirements: 5.5_

  - [x] 6.6 Implement mobile submenu container
    - Create div with overflow-hidden and height transition
    - Use conditional classes: `max-h-[500px]` when expanded, `max-h-0` when collapsed
    - Add transition: `transition-all duration-300`
    - Add left border with brandColor when active
    - _Requirements: 5.6, 5.7, 5.8_

  - [x] 6.7 Implement mobile submenu items
    - Loop through child menu items with ACL filtering
    - Resolve custom names for children
    - Detect active states
    - Apply indentation: `pl-10`
    - Apply same color scheme as desktop
    - _Requirements: 5.9, 12.4_

  - [ ] 6.8 Write property test for mobile drawer submenu toggle
    - **Property 9: Mobile drawer submenu toggle**
    - **Validates: Requirements 5.3**
    - Test clicking menu toggles submenu visibility
    - Run 100 iterations

  - [ ] 6.9 Write property test for mobile drawer arrow rotation
    - **Property 10: Mobile drawer arrow icon rotation**
    - **Validates: Requirements 5.5**
    - Test arrow rotates based on menu state
    - Run 100 iterations

  - [ ] 6.10 Write property test for mobile drawer active submenu border
    - **Property 11: Mobile drawer active submenu border**
    - **Validates: Requirements 5.8**
    - Test active submenu displays left border with brandColor
    - Run 100 iterations

  - [ ] 6.11 Write property test for mobile drawer auto-expands active menu
    - **Property 12: Mobile drawer auto-expands active menu**
    - **Validates: Requirements 5.10, 9.9**
    - Test drawer automatically expands parent menu containing active item
    - Run 100 iterations

  - [ ] 6.12 Write unit tests for mobile drawer
    - Test drawer opens and closes correctly
    - Test accordion behavior for submenus
    - Test active menu auto-expansion on mount

  - [x] 6.13 Git commit for mobile drawer component
    - Stage changes: `git add packages/Webkul/Admin/src/Resources/views/components/layouts/sidebar/mobile/index.blade.php`
    - Commit: `git commit -m "feat(admin): implement mobile drawer with accordion-style submenus"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "feat(admin): Mobile drawer with accordion navigation" --body "Part 6: Implement responsive mobile drawer with accordion-style submenus and auto-expansion"`


- [ ] 7. Header Component Updates
  - [x] 7.1 Update header structure and positioning
    - Update `packages/Webkul/Admin/src/Resources/views/components/layouts/header/index.blade.php`
    - Set sticky positioning: `sticky top-0`
    - Set z-index: `z-[10001]`
    - Apply responsive padding: `px-2 py-2 sm:px-4 sm:py-2.5`
    - Add background with dark mode: `bg-white dark:bg-gray-900`
    - _Requirements: 6.1, 6.2_

  - [x] 7.2 Update logo with responsive sizing
    - Apply responsive height: `h-8 sm:h-10`
    - Maintain existing logo rendering logic
    - _Requirements: 6.3_

  - [x] 7.3 Integrate hamburger menu for mobile
    - Include mobile drawer component toggle
    - Show only on mobile (handled by drawer component)
    - _Requirements: 6.4_

  - [x] 7.4 Update header action icons
    - Apply consistent icon sizing: `text-xl sm:text-2xl`
    - Apply hover states: `hover:bg-gray-100 dark:hover:bg-gray-950`
    - Add smooth transitions: `transition-colors duration-200`
    - Update spacing: `gap-1 sm:gap-2.5`
    - _Requirements: 6.6, 6.7, 6.8_

  - [ ] 7.5 Write property test for hamburger menu visibility on mobile
    - **Property 13: Hamburger menu visibility on mobile**
    - **Validates: Requirements 6.4, 8.1, 8.2**
    - Test hamburger shows below lg breakpoint
    - Test desktop sidebar hidden on mobile
    - Run 100 iterations

  - [ ] 7.6 Write unit tests for header component
    - Test header renders with correct positioning
    - Test responsive sizing for logo and icons
    - Test dark mode styling

  - [x] 7.7 Git commit for header component updates
    - Stage changes: `git add packages/Webkul/Admin/src/Resources/views/components/layouts/header/index.blade.php`
    - Commit: `git commit -m "feat(admin): update header component with responsive design and hamburger menu"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "feat(admin): Responsive header with hamburger menu" --body "Part 7: Update header component with responsive sizing and mobile hamburger menu integration"`


- [ ] 8. Layout Container Component
  - [x] 8.1 Update layout container structure
    - Update `packages/Webkul/Admin/src/Resources/views/components/layouts/index.blade.php`
    - Add group/container class to root div
    - Add conditional class binding for sidebar-collapsed state
    - _Requirements: 1.2_

  - [x] 8.2 Update content area padding
    - Set top padding: `pt-[60px]` to account for fixed header
    - Set responsive left padding for LTR: `lg:ltr:pl-[286px]` (expanded), `lg:group-[.sidebar-collapsed]/container:ltr:pl-[85px]` (collapsed)
    - Set responsive right padding for RTL: `lg:rtl:pr-[286px]` (expanded), `lg:group-[.sidebar-collapsed]/container:rtl:pr-[85px]` (collapsed)
    - Add smooth transitions: `transition-all duration-300`
    - _Requirements: 8.3_

  - [ ] 8.3 Write property test for content padding adjusts with sidebar state
    - **Property 17: Content padding adjusts with sidebar state**
    - **Validates: Requirements 8.3**
    - Test content padding changes based on sidebar collapsed/expanded state
    - Test both LTR and RTL directions
    - Run 100 iterations

  - [ ] 8.4 Write property test for responsive layout integrity
    - **Property 18: Responsive layout integrity**
    - **Validates: Requirements 8.7**
    - Test layout maintains proper structure at all breakpoints
    - Test no overlapping elements
    - Run 100 iterations

  - [ ] 8.5 Write unit tests for layout container
    - Test layout renders all child components
    - Test sidebar collapse state propagates correctly
    - Test responsive padding classes apply correctly

  - [x] 8.6 Git commit for layout container component
    - Stage changes: `git add packages/Webkul/Admin/src/Resources/views/components/layouts/index.blade.php`
    - Commit: `git commit -m "feat(admin): update layout container with responsive padding and sidebar state management"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "feat(admin): Layout container with dynamic padding" --body "Part 8: Update layout container to handle responsive padding based on sidebar state"`


- [ ] 9. Icon System Implementation
  - [ ] 9.1 Add new icon definitions to icomoon font
    - Add icon-right-arrow glyph
    - Add icon-left-arrow glyph
    - Add icon-arrow-up glyph
    - Add icon-arrow-down glyph
    - Regenerate icomoon font files (woff, woff2)
    - Update icon font files in `packages/Webkul/Admin/src/Resources/assets/fonts/`
    - _Requirements: 7.5_

  - [ ] 9.2 Verify existing icon compatibility
    - Test all existing icon classes still work
    - Verify icon-dashboard, icon-catalog, icon-customers, etc.
    - _Requirements: 7.6_

  - [ ] 9.3 Write property test for icon system font family consistency
    - **Property 14: Icon system font family consistency**
    - **Validates: Requirements 7.1, 7.2**
    - Test all icon elements use icomoon font family
    - Test all icons have icon- prefix
    - Run 100 iterations

  - [ ] 9.4 Write property test for icon system dark mode support
    - **Property 15: Icon system dark mode support**
    - **Validates: Requirements 7.4, 7.7**
    - Test icon colors adjust in dark mode
    - Run 100 iterations

  - [ ] 9.5 Write property test for icon system backward compatibility
    - **Property 16: Icon system backward compatibility**
    - **Validates: Requirements 7.6**
    - Test existing icon classes continue to work
    - Run 100 iterations

  - [ ] 9.6 Git commit for icon system implementation
    - Stage changes: `git add packages/Webkul/Admin/src/Resources/assets/fonts/*`
    - Commit: `git commit -m "feat(admin): add new arrow icons to icomoon font system"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "feat(admin): Enhanced icon system with new arrows" --body "Part 9: Add new arrow icons (right, left, up, down) to icomoon font system"`


- [ ] 10. Checkpoint - Core Implementation Complete
  - Ensure all tests pass, ask the user if questions arise.
  
  - [ ] 10.1 Git commit for checkpoint
    - Stage all changes: `git add .`
    - Commit: `git commit -m "chore(admin): checkpoint - core implementation complete"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "chore(admin): Checkpoint - Core implementation complete" --body "Checkpoint: All core components (sidebar, submenu, mobile drawer, header, layout) are implemented and tested"`

- [ ] 11. Interaction and Event Handling
  - [ ] 11.1 Implement click outside detection
    - Add window click event listener in mounted hook
    - Implement handleFocusOut to detect clicks outside sidebar and submenu
    - Reset isMenuActive and hoveringMenu on outside click
    - Clean up event listener in beforeUnmount
    - _Requirements: 9.5, 9.7_

  - [ ] 11.2 Implement keyboard navigation support
    - Add keydown event listener for Tab, Enter, Arrow keys, Escape
    - Implement focusNextMenuItem() helper
    - Implement focusPreviousMenuItem() helper
    - Implement openSubmenu() helper
    - Implement closeSubmenu() helper
    - Implement closeAllMenus() helper
    - _Requirements: 13.3_

  - [ ] 11.3 Write property test for click outside closes menu
    - **Property 19: Click outside closes menu**
    - **Validates: Requirements 9.5**
    - Test clicking outside closes menu and resets state
    - Run 100 iterations

  - [ ]* 11.4 Write property test for menu interaction idempotence
    - **Property 29: Menu interaction idempotence**
    - **Validates: Requirements 17.11**
    - Test hover → click → hover produces consistent behavior
    - Run 100 iterations

  - [ ] 11.5 Write unit tests for event handling
    - Test click outside detection works correctly
    - Test event listeners are cleaned up properly
    - Test keyboard navigation works for all keys

  - [ ] 11.6 Git commit for interaction and event handling
    - Stage changes: `git add packages/Webkul/Admin/src/Resources/assets/js/app.js`
    - Commit: `git commit -m "feat(admin): implement click outside detection and keyboard navigation"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "feat(admin): Advanced interaction and keyboard navigation" --body "Part 11: Add click-outside detection and full keyboard navigation support"`


- [ ] 12. Accessibility Enhancements
  - [ ] 12.1 Add semantic HTML and ARIA attributes
    - Use semantic nav element for navigation
    - Add role="list" and role="listitem" where appropriate
    - Add aria-label to all interactive elements
    - Add aria-current="page" to active menu items
    - Add aria-expanded and aria-haspopup for parent menus
    - Add aria-hidden="true" to decorative icons
    - _Requirements: 13.1, 13.2_

  - [ ] 12.2 Implement focus state styling
    - Add visible focus indicators: `focus:outline-none focus:ring-2 focus:ring-brandColor focus:ring-offset-2`
    - Ensure focus indicators meet WCAG visibility standards
    - Test focus states on all interactive elements
    - _Requirements: 13.4_

  - [ ] 12.3 Verify color contrast compliance
    - Test all text/background combinations with contrast checker
    - Ensure minimum 4.5:1 ratio for normal text
    - Ensure minimum 3:1 ratio for large text
    - Document any exceptions with justification
    - _Requirements: 13.5_

  - [ ]* 12.4 Write property test for semantic HTML for menu items
    - **Property 22: Semantic HTML for menu items**
    - **Validates: Requirements 13.1**
    - Test menu items use proper anchor tags
    - Run 100 iterations

  - [ ]* 12.5 Write property test for ARIA labels on icon-only elements
    - **Property 23: ARIA labels for icon-only elements**
    - **Validates: Requirements 13.2, 13.7**
    - Test icons without text have ARIA labels or title attributes
    - Run 100 iterations

  - [ ]* 12.6 Write property test for keyboard navigation support
    - **Property 24: Keyboard navigation support**
    - **Validates: Requirements 13.3**
    - Test all interactive elements accessible via keyboard
    - Run 100 iterations

  - [ ]* 12.7 Write property test for focus state visibility
    - **Property 25: Focus state visibility**
    - **Validates: Requirements 13.4**
    - Test focused elements display visible focus indicator
    - Run 100 iterations

  - [ ]* 12.8 Write property test for color contrast compliance
    - **Property 26: Color contrast compliance**
    - **Validates: Requirements 13.5**
    - Test all color combinations meet WCAG AA standards
    - Run 100 iterations

  - [ ] 12.9 Git commit for accessibility enhancements
    - Stage changes: `git add packages/Webkul/Admin/src/Resources/views/components/layouts/**/*.blade.php`
    - Commit: `git commit -m "feat(admin): add accessibility enhancements (ARIA labels, semantic HTML, focus states)"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "feat(admin): WCAG AA accessibility compliance" --body "Part 12: Add comprehensive accessibility features including ARIA labels, semantic HTML, focus states, and color contrast compliance"`


- [ ] 13. Dark Mode and RTL Support
  - [ ] 13.1 Verify dark mode styling for all components
    - Test sidebar in dark mode
    - Test submenu in dark mode
    - Test mobile drawer in dark mode
    - Test header in dark mode
    - Test all color utilities have dark: variants
    - _Requirements: 1.4, 15.4_

  - [ ] 13.2 Verify RTL layout support
    - Test sidebar positioning in RTL
    - Test submenu positioning in RTL (right-to-left)
    - Test arrow icons swap in RTL (left arrow instead of right)
    - Test padding and margins use logical properties
    - Test mobile drawer in RTL
    - _Requirements: 1.5_

  - [ ]* 13.3 Write property test for dark mode utility class support
    - **Property 28: Dark mode utility class support**
    - **Validates: Requirements 15.4**
    - Test all CSS utilities have dark mode variants
    - Run 100 iterations

  - [ ]* 13.4 Write property test for dark mode and RTL consistency
    - **Property 30: Dark mode and RTL support consistency**
    - **Validates: Requirements 1.4, 1.5**
    - Test components work correctly in dark mode AND RTL
    - Test no visual artifacts or broken layouts
    - Run 100 iterations

  - [ ]* 13.5 Write unit tests for dark mode
    - Test dark mode toggle switches all components
    - Test dark mode classes apply correctly
    - Test dark mode persists across page loads

  - [ ]* 13.6 Write unit tests for RTL support
    - Test RTL direction applies correct positioning
    - Test RTL arrow icons display correctly
    - Test RTL padding and margins are correct

  - [ ] 13.7 Git commit for dark mode and RTL support
    - Stage changes: `git add packages/Webkul/Admin/src/Resources/views/components/layouts/**/*.blade.php packages/Webkul/Admin/src/Resources/assets/css/app.css`
    - Commit: `git commit -m "feat(admin): verify and enhance dark mode and RTL layout support"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "feat(admin): Dark mode and RTL layout support" --body "Part 13: Complete dark mode styling and RTL (Right-to-Left) layout support for Arabic language"`


- [ ] 14. Checkpoint - Integration and Styling Complete
  - Ensure all tests pass, ask the user if questions arise.
  
  - [ ] 14.1 Git commit for checkpoint
    - Stage all changes: `git add .`
    - Commit: `git commit -m "chore(admin): checkpoint - integration and styling complete"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "chore(admin): Checkpoint - Integration and styling complete" --body "Checkpoint: All interactions, accessibility, dark mode, and RTL support are implemented"`

- [ ] 15. End-to-End Testing with Playwright
  - [ ]* 15.1 Write E2E test for sidebar hover expansion
    - Test sidebar expands on hover when collapsed
    - Test sidebar collapses when mouse moves away
    - Test sidebar remains expanded when clicked
    - _Requirements: 2.1, 2.2, 2.3, 17.1_

  - [ ]* 15.2 Write E2E test for submenu interaction
    - Test submenu appears on hover
    - Test submenu persists on click
    - Test submenu hides when clicking outside
    - _Requirements: 3.2, 3.6, 3.7, 3.8, 17.2_

  - [ ]* 15.3 Write E2E test for mobile drawer
    - Test drawer opens with hamburger menu
    - Test accordion behavior for submenus
    - Test drawer closes correctly
    - _Requirements: 5.3, 5.10, 17.3_

  - [ ]* 15.4 Write E2E test for keyboard navigation
    - Test Tab navigation between menu items
    - Test Arrow key navigation
    - Test Enter key activates menu items
    - Test Escape key closes menus
    - _Requirements: 13.3, 17.7_

  - [ ]* 15.5 Write E2E test for responsive behavior
    - Test layout at all breakpoints (sm, md, lg, xl)
    - Test desktop sidebar hides on mobile
    - Test mobile drawer shows on mobile
    - Test content padding adjusts correctly
    - _Requirements: 8.1, 8.2, 8.3, 8.7, 17.6_

  - [ ]* 15.6 Write E2E test for dark mode
    - Test dark mode toggle switches all components
    - Test dark mode styling applies correctly
    - Test dark mode persists across navigation
    - _Requirements: 1.4, 17.4_

  - [ ]* 15.7 Write E2E test for RTL layout
    - Test RTL direction applies correct positioning
    - Test submenu appears on left side in RTL
    - Test arrow icons swap correctly
    - _Requirements: 1.5, 17.5_

  - [ ]* 15.8 Write E2E test for custom menu names
    - Test custom menu names display correctly
    - Test fallback to default names when no custom name
    - _Requirements: 12.1, 12.2, 12.3_

  - [ ]* 15.9 Write E2E test for ACL integration
    - Test menu items filter based on permissions
    - Test users without permission don't see menu items
    - _Requirements: 14.2, 14.3_

  - [ ] 15.10 Git commit for E2E tests
    - Stage changes: `git add Admin/tests/e2e-pw/tests/**/*`
    - Commit: `git commit -m "test(admin): add comprehensive E2E tests with Playwright"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "test(admin): Comprehensive E2E test suite" --body "Part 15: Add end-to-end tests covering sidebar, submenu, mobile drawer, keyboard navigation, responsive behavior, dark mode, RTL, and ACL integration"`


- [ ] 16. Performance Optimization and Testing
  - [ ] 16.1 Optimize CSS bundle
    - Run PurgeCSS via Tailwind to remove unused styles
    - Verify CSS bundle size increase is less than 10%
    - Add CSS containment for sidebar component
    - Add GPU acceleration hints (will-change, transform: translateZ(0))
    - _Requirements: 19.5_

  - [ ] 16.2 Optimize JavaScript bundle
    - Implement code splitting for admin panel
    - Verify JS bundle size increase is less than 5%
    - Implement debouncing for hover events (50ms)
    - Use event delegation for menu items
    - _Requirements: 19.6_

  - [ ] 16.3 Optimize rendering performance
    - Use v-show instead of v-if for frequently toggled elements
    - Cache menu configuration in localStorage
    - Minimize DOM queries by caching references
    - _Requirements: 19.1, 19.2_

  - [ ]* 16.4 Write performance test for sidebar render time
    - Test sidebar renders in less than 100ms
    - _Requirements: 19.1_

  - [ ]* 16.5 Write performance test for animation frame rate
    - Test hover animations maintain 60fps
    - Test submenu animations maintain 60fps
    - _Requirements: 19.2, 11.7_

  - [ ]* 16.6 Write performance test for submenu appearance
    - Test submenu appears within 50ms of hover
    - _Requirements: 19.3_

  - [ ]* 16.7 Write performance test for mobile drawer
    - Test drawer opens within 300ms
    - _Requirements: 19.4_

  - [ ]* 16.8 Write performance test for Core Web Vitals
    - Test LCP < 2.5s
    - Test FID < 100ms
    - Test CLS < 0.1
    - _Requirements: 19.7_

  - [ ] 16.9 Git commit for performance optimization
    - Stage changes: `git add packages/Webkul/Admin/src/Resources/assets/**/*`
    - Commit: `git commit -m "perf(admin): optimize CSS/JS bundles and rendering performance"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "perf(admin): Performance optimization" --body "Part 16: Optimize CSS/JS bundles, implement code splitting, debouncing, and ensure Core Web Vitals compliance"`


- [ ] 17. Browser Compatibility and Polyfills
  - [ ] 17.1 Add feature detection for CSS features
    - Check for :has() selector support with fallback
    - Check for CSS Grid support with flexbox fallback
    - Check for CSS custom properties with static color fallback
    - Add appropriate body classes for unsupported features
    - _Requirements: 20.5, 20.6_

  - [ ] 17.2 Add JavaScript polyfills
    - Add IntersectionObserver polyfill for older browsers
    - Add ResizeObserver polyfill for older browsers
    - Load polyfills conditionally based on feature detection
    - _Requirements: 20.5_

  - [ ]* 17.3 Write browser compatibility tests
    - Test on Chrome 90+
    - Test on Firefox 88+
    - Test on Safari 14+
    - Test on Edge 90+
    - Document any browser-specific issues
    - _Requirements: 20.1, 20.2, 20.3, 20.4, 20.7_

  - [ ] 17.4 Git commit for browser compatibility
    - Stage changes: `git add packages/Webkul/Admin/src/Resources/assets/js/app.js`
    - Commit: `git commit -m "feat(admin): add feature detection and polyfills for browser compatibility"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "feat(admin): Browser compatibility and polyfills" --body "Part 17: Add feature detection and polyfills for Chrome 90+, Firefox 88+, Safari 14+, Edge 90+"`


- [ ] 18. Visual Regression Testing
  - [ ]* 18.1 Write visual regression test for sidebar states
    - Capture screenshot of sidebar in expanded state
    - Capture screenshot of sidebar in collapsed state
    - Capture screenshot of sidebar in dark mode
    - Capture screenshot of sidebar in RTL layout
    - Compare against baseline images
    - _Requirements: 17.1, 17.4, 17.5_

  - [ ]* 18.2 Write visual regression test for submenu
    - Capture screenshot of submenu in LTR
    - Capture screenshot of submenu in RTL
    - Capture screenshot of submenu in dark mode
    - Compare against baseline images
    - _Requirements: 3.5, 17.4, 17.5_

  - [ ]* 18.3 Write visual regression test for mobile drawer
    - Capture screenshot of mobile drawer open
    - Capture screenshot of mobile drawer with expanded submenu
    - Capture screenshot in dark mode
    - Compare against baseline images
    - _Requirements: 5.1, 17.4_

  - [ ]* 18.4 Write visual regression test for responsive breakpoints
    - Capture screenshots at all breakpoints (sm, md, lg, xl)
    - Verify layout integrity at each breakpoint
    - Compare against baseline images
    - _Requirements: 8.7, 17.6_

  - [ ] 18.5 Git commit for visual regression tests
    - Stage changes: `git add Admin/tests/e2e-pw/tests/**/*`
    - Commit: `git commit -m "test(admin): add visual regression tests for UI consistency"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "test(admin): Visual regression testing" --body "Part 18: Add visual regression tests for sidebar states, submenu, mobile drawer, and responsive breakpoints"`ainst baseline images
    - _Requirements: 8.7, 17.6_


- [ ] 19. Integration Testing
  - [ ]* 19.1 Write integration test for ACL and menu filtering
    - Create test user with specific permissions
    - Verify menu items filter correctly based on permissions
    - Test recursive filtering of child menus
    - Test parent menu removal when all children filtered
    - _Requirements: 14.1, 14.2, 14.3, 14.4_

  - [ ]* 19.2 Write integration test for custom menu names
    - Set custom menu names in configuration
    - Verify custom names display in sidebar
    - Verify custom names display in mobile drawer
    - Verify fallback to default names when no custom name
    - Test multi-language custom names
    - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5_

  - [ ]* 19.3 Write integration test for localStorage persistence
    - Toggle sidebar collapsed state
    - Refresh page
    - Verify sidebar state persists
    - Clear localStorage
    - Verify sidebar returns to default state
    - _Requirements: 1.2_

  - [ ]* 19.4 Write integration test for dark mode persistence
    - Toggle dark mode
    - Navigate to different pages
    - Verify dark mode persists across navigation
    - Refresh page
    - Verify dark mode persists across refresh
    - _Requirements: 1.4_

  - [ ] 19.5 Git commit for integration tests
    - Stage changes: `git add packages/Webkul/Admin/tests/**/*`
    - Commit: `git commit -m "test(admin): add integration tests for ACL, custom names, and persistence"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "test(admin): Integration test suite" --body "Part 19: Add integration tests for ACL filtering, custom menu names, localStorage persistence, and dark mode"`


- [ ] 20. Checkpoint - Testing Complete
  - Ensure all tests pass, ask the user if questions arise.
  
  - [ ] 20.1 Git commit for checkpoint
    - Stage all test files: `git add packages/Webkul/Admin/tests/**/* Admin/tests/e2e-pw/tests/**/*`
    - Commit: `git commit -m "chore(admin): checkpoint - all tests complete and passing"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "chore(admin): Checkpoint - All tests complete" --body "Checkpoint: All property-based, unit, integration, E2E, and visual regression tests are complete and passing"`

- [ ] 21. Documentation
  - [ ] 21.1 Create user documentation
    - Document desktop navigation usage
    - Document mobile navigation usage
    - Document keyboard navigation shortcuts
    - Document customization options (custom menu names, brand color)
    - Create before/after screenshots
    - Translate documentation to Arabic
    - _Requirements: 18.1, 18.8_

  - [ ] 21.2 Create developer documentation
    - Document file structure and modified files
    - Document new CSS classes and their usage
    - Document new Vue.js methods and parameters
    - Document helper functions (resolveMenuName, filterMenuByPermissions)
    - Provide code examples for common customizations
    - Document how to add new menu items
    - Document how to customize brand color
    - Document how to extend sidebar behavior
    - _Requirements: 18.2, 18.3, 18.4, 18.5_

  - [ ] 21.3 Document breaking changes and migration steps
    - List all breaking changes (if any)
    - Provide migration guide from old UI to new UI
    - Document rollback procedure
    - Document updated class names and their replacements
    - _Requirements: 16.5, 16.6, 16.7, 18.6_

  - [ ] 21.4 Create troubleshooting guide
    - Document common issues and solutions
    - Document browser compatibility issues
    - Document performance optimization tips
    - Document debugging techniques
    - _Requirements: 18.7_

  - [ ] 21.5 Update API documentation
    - Document Vue.js component API
    - Document helper function signatures
    - Document event handlers and their parameters
    - Provide JSDoc comments for all methods
    - _Requirements: 18.4_

  - [ ] 21.6 Git commit for documentation
    - Stage changes: `git add docs/**/* README.md`
    - Commit: `git commit -m "docs(admin): add comprehensive documentation for UI redesign"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "docs(admin): Complete documentation" --body "Part 21: Add user documentation, developer documentation, migration guide, troubleshooting guide, and API documentation (English and Arabic)"`


- [ ] 22. Security Review and Hardening
  - [ ] 22.1 Implement XSS prevention
    - Verify all user input is sanitized (custom menu names)
    - Verify Blade automatic escaping is used correctly
    - Review use of {!! !!} syntax (should only be for trusted content)
    - Add input validation for custom menu names
    - _Requirements: Security best practices_

  - [ ] 22.2 Verify CSRF protection
    - Ensure CSRF token included in all AJAX requests
    - Verify axios default headers include CSRF token
    - Test CSRF protection on all form submissions
    - _Requirements: Security best practices_

  - [ ] 22.3 Implement Content Security Policy
    - Add CSP headers for admin panel
    - Allow inline styles and scripts only where necessary
    - Whitelist font sources for icomoon
    - Test CSP doesn't break functionality
    - _Requirements: Security best practices_

  - [ ] 22.4 Verify permission checks
    - Ensure all menu items check permissions server-side
    - Never trust client-side permission checks
    - Test unauthorized access attempts return 403
    - Verify ACL integration is secure
    - _Requirements: 14.2, 14.3, 14.4_

  - [ ]* 22.5 Write security tests
    - Test XSS prevention in custom menu names
    - Test CSRF protection on state changes
    - Test unauthorized menu access blocked
    - Test SQL injection prevention (if applicable)

  - [ ] 22.6 Git commit for security enhancements
    - Stage changes: `git add packages/Webkul/Admin/src/**/*`
    - Commit: `git commit -m "security(admin): implement XSS prevention, CSRF protection, and CSP headers"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "security(admin): Security hardening" --body "Part 22: Implement XSS prevention, CSRF protection, Content Security Policy, and secure permission checks"`


- [ ] 23. Final Integration and Wiring
  - [ ] 23.1 Verify all components work together
    - Test complete user flow: login → navigate sidebar → open submenu → navigate to page
    - Test mobile flow: login → open drawer → expand submenu → navigate to page
    - Test sidebar collapse → hover → click → navigate flow
    - Test dark mode toggle affects all components
    - Test RTL direction affects all components
    - _Requirements: All requirements_

  - [ ] 23.2 Verify backward compatibility
    - Test existing admin panel functionality still works
    - Test existing packages integrate correctly
    - Test existing custom themes don't break
    - Test existing menu configurations load correctly
    - _Requirements: 16.4_

  - [ ] 23.3 Verify asset compilation
    - Run `npm run build` to compile production assets
    - Verify CSS bundle size is acceptable
    - Verify JS bundle size is acceptable
    - Test compiled assets work in production mode
    - _Requirements: 19.5, 19.6_

  - [ ] 23.4 Clean up and code review
    - Remove any console.log statements
    - Remove commented-out code
    - Verify code follows Laravel and Vue.js best practices
    - Verify code is properly formatted
    - Add missing comments where needed
    - _Requirements: Code quality_

  - [ ]* 23.5 Write final integration tests
    - Test complete user journey from login to navigation
    - Test all components integrate correctly
    - Test no console errors or warnings
    - Test no visual glitches or artifacts

  - [ ] 23.6 Git commit for final integration
    - Stage all changes: `git add .`
    - Commit: `git commit -m "feat(admin): complete UI redesign integration and final wiring"`
    - Push: `git push origin feature/admin-ui-redesign`
    - Create PR: `gh pr create --title "feat(admin): Final integration and wiring" --body "Part 23: Complete final integration, verify all components work together, ensure backward compatibility, and optimize production assets"`


- [ ] 24. Final Checkpoint and Deployment Preparation
  - Ensure all tests pass, ask the user if questions arise.
  
  - [ ] 24.1 Create final comprehensive pull request
    - Ensure all previous PRs are merged or closed
    - Push final commits: `git push origin feature/admin-ui-redesign`
    - Create comprehensive PR with detailed description:
      ```bash
      gh pr create --title "feat(admin): Complete UI redesign with enhanced navigation and responsive design" --body "
      ## 🎨 Admin UI Redesign - Complete Implementation
      
      ### 📋 Summary
      Complete redesign of MedSDN Admin Panel UI with modern sidebar navigation, enhanced hover interactions, responsive mobile drawer, and cohesive design system.
      
      ### ✨ Key Features
      - ✅ Modern sidebar with expand/collapse functionality
      - ✅ Hover-based submenu system with RTL support
      - ✅ Responsive mobile drawer with accordion navigation
      - ✅ Dark mode support across all components
      - ✅ WCAG AA accessibility compliance
      - ✅ Custom menu names support
      - ✅ ACL integration maintained
      - ✅ Performance optimized (60fps animations)
      - ✅ Browser compatibility (Chrome 90+, Firefox 88+, Safari 14+, Edge 90+)
      
      ### 📸 Screenshots
      See docs/screenshots/ for before/after comparisons
      
      ### 📦 Modified Files
      - Tailwind config and CSS utilities
      - Vue.js state management
      - All layout components (sidebar, header, mobile drawer)
      - Icon font system
      - Helper functions
      
      ### 🧪 Testing
      - [x] 30 Property-based tests (100 iterations each)
      - [x] Unit tests for all components
      - [x] Integration tests
      - [x] E2E tests with Playwright
      - [x] Visual regression tests
      - [x] Performance tests
      - [x] Accessibility tests
      - [x] Security tests
      
      ### ⚠️ Breaking Changes
      None - Fully backward compatible
      
      ### 📚 Documentation
      Complete documentation in English and Arabic
      
      ### ✅ Checklist
      - [x] All tests passing
      - [x] Documentation complete
      - [x] Performance benchmarks met
      - [x] Security review completed
      "
      ```
    - Add labels: `enhancement`, `ui`, `admin`, `ready-for-review`
    - Request review from: `@team-lead @frontend-lead @backend-lead`
    - Link to related issues/tickets

## Notes

- Tasks marked with `*` are optional testing tasks and can be skipped for faster MVP delivery
- Each task references specific requirements for traceability
- Property-based tests validate universal correctness properties across random inputs
- Unit tests validate specific examples and edge cases
- E2E tests validate complete user workflows
- Integration tests validate component interactions
- Checkpoints ensure incremental validation and provide opportunities for user feedback
- All code should be production-ready and follow Laravel/Vue.js best practices
- Security and accessibility are non-negotiable requirements

## Implementation Strategy

The tasks are organized in a bottom-up approach:

1. **Foundation (Tasks 1-3)**: Configuration, utilities, and helper functions
2. **Core Components (Tasks 4-8)**: Desktop sidebar, submenu, mobile drawer, header, layout
3. **Enhancements (Tasks 9-13)**: Icons, interactions, accessibility, dark mode, RTL
4. **Testing (Tasks 14-20)**: Comprehensive testing at all levels
5. **Documentation (Task 21)**: User and developer documentation
6. **Security (Task 22)**: Security review and hardening
7. **Integration (Tasks 23-24)**: Final integration, wiring, and deployment preparation

This approach ensures each layer is solid before building the next, with regular checkpoints for validation and user feedback.

## Testing Coverage

The implementation includes comprehensive testing:

- **30 Property-Based Tests**: Validate universal properties across random inputs (100 iterations each)
- **Unit Tests**: Test individual components and functions
- **Integration Tests**: Test component interactions and data flow
- **E2E Tests**: Test complete user workflows with Playwright
- **Visual Regression Tests**: Catch unintended UI changes
- **Performance Tests**: Ensure performance targets are met
- **Accessibility Tests**: Verify WCAG AA compliance
- **Security Tests**: Validate security measures

## Files Modified

The following files will be created or modified during implementation:

**Configuration:**
- `packages/Webkul/Admin/tailwind.config.js`
- `packages/Webkul/Admin/src/Resources/assets/css/app.css`

**JavaScript:**
- `packages/Webkul/Admin/src/Resources/assets/js/app.js`

**Blade Templates:**
- `packages/Webkul/Admin/src/Resources/views/components/layouts/index.blade.php`
- `packages/Webkul/Admin/src/Resources/views/components/layouts/header/index.blade.php`
- `packages/Webkul/Admin/src/Resources/views/components/layouts/sidebar/desktop/index.blade.php`
- `packages/Webkul/Admin/src/Resources/views/components/layouts/sidebar/mobile/index.blade.php`

**Assets:**
- `packages/Webkul/Admin/src/Resources/assets/fonts/icomoon.woff`
- `packages/Webkul/Admin/src/Resources/assets/fonts/icomoon.woff2`

**Tests:**
- Property-based tests in `packages/Webkul/Admin/tests/Feature/`
- Unit tests in `packages/Webkul/Admin/tests/Unit/`
- E2E tests in `tests/Browser/`

**Documentation:**
- User guide (English and Arabic)
- Developer documentation
- Migration guide
- Troubleshooting guide
