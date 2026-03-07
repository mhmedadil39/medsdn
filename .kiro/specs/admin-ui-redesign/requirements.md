# Requirements Document

## Introduction

هذا المستند يحدد متطلبات تحديث تصميم واجهة لوحة التحكم (Admin Panel) في منصة MedSDN لتطابق التصميم الجديد الموجود في المسار `/home/hmam/Documents/NEW/medsdn/Admin`. التحديث يشمل إعادة تصميم شاملة للواجهة مع الحفاظ على جميع الوظائف الحالية وتحسين تجربة المستخدم.

## Glossary

- **Admin_Panel**: لوحة التحكم الإدارية للمنصة
- **Sidebar**: القائمة الجانبية للتنقل
- **Header**: الشريط العلوي للواجهة
- **Menu_System**: نظام القوائم الديناميكي
- **Dark_Mode**: وضع الألوان الداكنة
- **RTL_Support**: دعم اللغات من اليمين لليسار
- **Mega_Search**: نظام البحث الشامل
- **Quick_Creation**: نظام الإنشاء السريع
- **Notification_System**: نظام الإشعارات
- **User_Profile**: ملف تعريف المستخدم الإداري
- **Responsive_Design**: التصميم المتجاوب
- **Vue_Components**: مكونات Vue.js
- **Blade_Templates**: قوالب Blade
- **Tailwind_CSS**: إطار عمل Tailwind للتنسيق
- **Icon_System**: نظام الأيقونات المخصص
- **ACL_System**: نظام التحكم بالصلاحيات
- **Submenu**: القوائم الفرعية
- **Collapsed_State**: حالة القائمة المطوية
- **Expanded_State**: حالة القائمة الموسعة
- **Hover_Behavior**: سلوك التفاعل عند التمرير
- **Active_State**: حالة العنصر النشط
- **Brand_Color**: اللون الأساسي للعلامة التجارية
- **Transition_Animation**: الحركات الانتقالية
- **Mobile_Drawer**: القائمة المنسدلة للأجهزة المحمولة
- **Desktop_Sidebar**: القائمة الجانبية لأجهزة سطح المكتب

## Requirements

### Requirement 1: تحديث بنية Sidebar الرئيسية

**User Story:** كمطور، أريد تحديث بنية Sidebar لتطابق التصميم الجديد، حتى يتم تحسين تجربة التنقل.

#### Acceptance Criteria

1. THE Sidebar SHALL use a fixed position at the top of 60px with a width of 200px in expanded state
2. THE Sidebar SHALL transition to a width of 70px in collapsed state
3. WHEN the sidebar is in collapsed state, THE Sidebar SHALL hide menu text and show only icons
4. THE Sidebar SHALL apply dark mode styling when dark mode is enabled
5. THE Sidebar SHALL support RTL layout direction
6. THE Sidebar SHALL use the class `group-[.sidebar-collapsed]/container:w-[70px]` for responsive width
7. THE Sidebar SHALL maintain a z-index of 10002 for proper layering
8. THE Sidebar SHALL include smooth transitions with duration-80 class

### Requirement 2: تطبيق نظام Hover للقائمة الجانبية

**User Story:** كمستخدم إداري، أريد أن تتوسع القائمة الجانبية عند التمرير عليها، حتى أتمكن من رؤية أسماء القوائم بسهولة.

#### Acceptance Criteria

1. WHEN the user hovers over the collapsed sidebar, THE Sidebar SHALL expand to full width
2. WHEN the user moves the mouse away from the sidebar, THE Sidebar SHALL collapse back to icon-only width
3. IF the menu is actively clicked (isMenuActive is true), THEN THE Sidebar SHALL remain expanded regardless of hover state
4. THE Sidebar SHALL use `handleMouseOver` and `handleMouseLeave` methods for hover behavior
5. THE Sidebar SHALL apply smooth CSS transitions during expand/collapse animations
6. WHILE the sidebar is collapsed, THE Sidebar SHALL show tooltips or expand on hover for better UX

### Requirement 3: تحديث نظام القوائم الفرعية (Submenu)

**User Story:** كمستخدم إداري، أريد رؤية القوائم الفرعية بشكل منفصل، حتى أتمكن من التنقل بشكل أفضل.

#### Acceptance Criteria

1. WHEN a menu item has children, THE Menu_System SHALL display a right arrow icon (icon-right-arrow)
2. WHEN the user hovers over a parent menu item, THE Submenu SHALL appear in a separate panel to the right
3. THE Submenu SHALL be positioned at `ltr:left-[200px] rtl:right-[199px]` relative to the sidebar
4. THE Submenu SHALL have a minimum width of 140px and maximum width of max-content
5. THE Submenu SHALL use the same styling as the main sidebar (white background, dark mode support)
6. WHEN the user clicks on a menu item, THE Menu_System SHALL toggle the `isMenuActive` state
7. IF `isMenuActive` is true AND `hoveringMenu` matches the menu key, THEN THE Submenu SHALL remain visible
8. THE Submenu SHALL hide when the user moves away from both the parent menu and submenu area
9. WHERE a menu item is 'settings' or 'configuration', THE Menu_System SHALL use default behavior without hover submenu

### Requirement 4: تطبيق نظام الألوان الجديد

**User Story:** كمطور، أريد تطبيق نظام الألوان الجديد، حتى تتطابق الواجهة مع هوية العلامة التجارية.

#### Acceptance Criteria

1. THE Admin_Panel SHALL use `bg-brandColor` class for active menu items instead of `bg-blue-600`
2. THE Admin_Panel SHALL define `brandColor` in Tailwind configuration
3. WHEN a menu item is active, THE Menu_System SHALL apply white text color with `bg-brandColor` background
4. WHEN a menu item is inactive, THE Menu_System SHALL use gray-600 text color (dark:gray-300 in dark mode)
5. THE Admin_Panel SHALL maintain hover states with `hover:bg-gray-100` (dark:hover:bg-gray-950)
6. THE Admin_Panel SHALL use consistent color scheme across all components

### Requirement 5: تحديث Mobile Sidebar

**User Story:** كمستخدم على جهاز محمول، أريد قائمة جانبية محسّنة للأجهزة المحمولة، حتى أتمكن من التنقل بسهولة.

#### Acceptance Criteria

1. THE Mobile_Drawer SHALL use a drawer component with 280px width
2. THE Mobile_Drawer SHALL support dynamic width with `[&>:nth-child(3)]:max-sm:!w-[80%]` for small screens
3. WHEN a menu item with children is clicked, THE Mobile_Drawer SHALL toggle the submenu visibility
4. THE Mobile_Drawer SHALL use `activeMenu` state to track which menu is expanded
5. THE Mobile_Drawer SHALL display an arrow icon that rotates based on menu state (icon-arrow-up/icon-arrow-down)
6. THE Submenu SHALL expand with smooth animation using `max-h-[500px]` when active
7. THE Submenu SHALL collapse to `max-h-0` when inactive
8. THE Submenu SHALL use left border with `border-l-brandColor` when active
9. THE Submenu SHALL indent submenu items with `pl-10` padding
10. WHEN the page loads, THE Mobile_Drawer SHALL automatically expand the menu containing the active page

### Requirement 6: تحديث Header Component

**User Story:** كمطور، أريد تحديث مكون Header ليتوافق مع التصميم الجديد، حتى يكون متسقاً مع باقي الواجهة.

#### Acceptance Criteria

1. THE Header SHALL maintain sticky positioning at the top with z-index 10001
2. THE Header SHALL use consistent padding: `px-2 py-2 sm:px-4 sm:py-2.5`
3. THE Header SHALL display logo with responsive sizing: `h-8 sm:h-10`
4. THE Header SHALL include hamburger menu icon for mobile devices
5. THE Header SHALL integrate Mega_Search component with responsive widths
6. THE Header SHALL display dark mode toggle, shop link, notifications, and user profile
7. THE Header SHALL use icon sizes of `text-xl sm:text-2xl` for consistency
8. THE Header SHALL apply hover states with `hover:bg-gray-100 dark:hover:bg-gray-950`

### Requirement 7: تحسين نظام الأيقونات

**User Story:** كمطور، أريد استخدام نظام أيقونات محسّن، حتى تكون الأيقونات واضحة ومتسقة.

#### Acceptance Criteria

1. THE Icon_System SHALL use icomoon font family for all icons
2. THE Icon_System SHALL define all icon classes with `icon-` prefix
3. THE Icon_System SHALL apply consistent sizing using Tailwind classes (text-xl, text-2xl)
4. THE Icon_System SHALL support dark mode with automatic color adjustment
5. THE Icon_System SHALL include new icons: icon-right-arrow, icon-left-arrow, icon-arrow-up, icon-arrow-down
6. THE Icon_System SHALL maintain existing icons for backward compatibility
7. THE Icon_System SHALL use gray-500 color by default (dark:gray-300 in dark mode)

### Requirement 8: تطبيق Responsive Design المحسّن

**User Story:** كمستخدم، أريد واجهة متجاوبة تعمل بشكل مثالي على جميع الأجهزة، حتى أتمكن من استخدام لوحة التحكم من أي جهاز.

#### Acceptance Criteria

1. THE Admin_Panel SHALL hide desktop sidebar on screens smaller than lg breakpoint (1024px)
2. THE Admin_Panel SHALL show mobile drawer on screens smaller than lg breakpoint
3. THE Admin_Panel SHALL adjust content padding based on sidebar state: `lg:ltr:pl-[286px]` for expanded, `lg:group-[.sidebar-collapsed]/container:ltr:pl-[85px]` for collapsed
4. THE Admin_Panel SHALL use responsive text sizes: `text-sm sm:text-base`
5. THE Admin_Panel SHALL adjust icon sizes: `text-xl sm:text-2xl`
6. THE Admin_Panel SHALL use responsive spacing: `gap-1 sm:gap-2.5`
7. THE Admin_Panel SHALL maintain proper layout on all screen sizes (sm: 525px, md: 768px, lg: 1024px, xl: 1240px)

### Requirement 9: تحديث Vue.js Components

**User Story:** كمطور، أريد تحديث مكونات Vue.js لتتوافق مع التصميم الجديد، حتى تعمل جميع التفاعلات بشكل صحيح.

#### Acceptance Criteria

1. THE Vue_Components SHALL include `isMenuActive` state in root app component
2. THE Vue_Components SHALL include `hoveringMenu` state to track which menu is being hovered
3. THE Vue_Components SHALL implement `handleMouseOver` method for sidebar expansion
4. THE Vue_Components SHALL implement `handleMouseLeave` method for sidebar collapse
5. THE Vue_Components SHALL implement `handleFocusOut` method to close menu when clicking outside
6. THE Vue_Components SHALL use event listeners for click events on window
7. THE Vue_Components SHALL properly clean up event listeners in beforeDestroy hook
8. THE Mobile_Drawer component SHALL implement `toggleMenu` method for menu expansion
9. THE Mobile_Drawer component SHALL automatically detect and expand active menu on mount

### Requirement 10: تحديث Tailwind Configuration

**User Story:** كمطور، أريد تحديث تكوين Tailwind ليشمل الألوان والإعدادات الجديدة، حتى تكون جميع الأنماط متاحة.

#### Acceptance Criteria

1. THE Tailwind_CSS configuration SHALL define `brandColor` in the colors extend section
2. THE Tailwind_CSS configuration SHALL maintain existing color definitions (darkGreen, darkBlue, darkPink)
3. THE Tailwind_CSS configuration SHALL include icon font family in fontFamily extend
4. THE Tailwind_CSS configuration SHALL safelist all icon classes with pattern `/icon-/`
5. THE Tailwind_CSS configuration SHALL maintain existing screen breakpoints
6. THE Tailwind_CSS configuration SHALL support dark mode with 'class' strategy

### Requirement 11: تطبيق Transition Animations

**User Story:** كمستخدم، أريد حركات انتقالية سلسة، حتى تكون تجربة الاستخدام أكثر سلاسة.

#### Acceptance Criteria

1. THE Sidebar SHALL use `transition-all duration-300` for width changes
2. THE Submenu SHALL use `transition-all duration-300` for visibility changes
3. THE Mobile_Drawer submenu SHALL use `transition-all duration-300` for height changes
4. THE Menu items SHALL use `transition-colors duration-200` for color changes
5. THE Arrow icons SHALL use `transition-transform duration-300` for rotation
6. THE Hover states SHALL apply smooth transitions for background color changes
7. ALL animations SHALL maintain 60fps performance

### Requirement 12: دعم Customizable Menu Names

**User Story:** كمسؤول نظام، أريد إمكانية تخصيص أسماء القوائم، حتى أتمكن من تعديل النصوص حسب الحاجة.

#### Acceptance Criteria

1. THE Menu_System SHALL check for custom menu names in configuration using `core()->getConfigData('general.settings.menu.'.$menuItem->getKey())`
2. IF a custom menu name exists, THEN THE Menu_System SHALL display the custom name
3. IF no custom menu name exists, THEN THE Menu_System SHALL display the default name from `$menuItem->getName()`
4. THE Menu_System SHALL apply this logic to both parent menus and submenus
5. THE Menu_System SHALL support multi-language custom names

### Requirement 13: تحسين Accessibility

**User Story:** كمستخدم ذو احتياجات خاصة، أريد واجهة يمكن الوصول إليها، حتى أتمكن من استخدام لوحة التحكم بسهولة.

#### Acceptance Criteria

1. THE Menu items SHALL use semantic HTML with proper anchor tags
2. THE Icon elements SHALL include appropriate ARIA labels where needed
3. THE Interactive elements SHALL be keyboard accessible
4. THE Focus states SHALL be clearly visible
5. THE Color contrast SHALL meet WCAG AA standards
6. THE Menu_System SHALL support screen readers
7. THE Collapsed sidebar SHALL provide alternative text or tooltips for icon-only items

### Requirement 14: الحفاظ على ACL Integration

**User Story:** كمطور، أريد الحفاظ على تكامل نظام الصلاحيات، حتى تظهر القوائم حسب صلاحيات المستخدم.

#### Acceptance Criteria

1. THE Menu_System SHALL continue using `menu()->getItems('admin')` to fetch menu items
2. THE Menu_System SHALL respect ACL permissions for menu visibility
3. THE Menu_System SHALL hide menu items that the user doesn't have permission to access
4. THE Menu_System SHALL maintain existing ACL logic without modifications
5. THE Menu_System SHALL work seamlessly with the existing Bouncer system

### Requirement 15: تحديث CSS Utilities

**User Story:** كمطور، أريد تحديث CSS utilities لتشمل الأنماط الجديدة، حتى تكون متاحة في جميع المكونات.

#### Acceptance Criteria

1. THE CSS utilities SHALL include `.sidebar-rounded` class for sidebar styling
2. THE CSS utilities SHALL maintain existing button classes (primary-button, secondary-button, transparent-button)
3. THE CSS utilities SHALL include `.journal-scroll` class for custom scrollbar styling
4. THE CSS utilities SHALL support dark mode for all utility classes
5. THE CSS utilities SHALL include `.box-shadow` utility for consistent shadows
6. THE CSS utilities SHALL maintain `.shimmer` animation for loading states

### Requirement 16: Migration Strategy

**User Story:** كمطور، أريد استراتيجية هجرة واضحة، حتى أتمكن من تطبيق التحديثات بأمان.

#### Acceptance Criteria

1. THE migration SHALL create backup of existing layout files before modifications
2. THE migration SHALL update files in the following order: Tailwind config, CSS, Blade templates, Vue components
3. THE migration SHALL test each component after update to ensure functionality
4. THE migration SHALL maintain backward compatibility where possible
5. THE migration SHALL document all breaking changes
6. THE migration SHALL provide rollback procedure in case of issues
7. THE migration SHALL update all references to old class names

### Requirement 17: Testing Requirements

**User Story:** كمطور، أريد متطلبات اختبار شاملة، حتى أضمن جودة التحديثات.

#### Acceptance Criteria

1. THE testing SHALL verify sidebar expand/collapse functionality
2. THE testing SHALL verify submenu hover and click behavior
3. THE testing SHALL verify mobile drawer functionality
4. THE testing SHALL verify dark mode switching
5. THE testing SHALL verify RTL layout support
6. THE testing SHALL verify responsive behavior on all breakpoints
7. THE testing SHALL verify keyboard navigation
8. THE testing SHALL verify menu active states
9. THE testing SHALL verify custom menu names display
10. THE testing SHALL verify ACL integration
11. FOR ALL valid menu configurations, THE testing SHALL verify that hovering then clicking then hovering produces consistent behavior (idempotence property)
12. FOR ALL sidebar states (collapsed/expanded), THE testing SHALL verify that toggling twice returns to original state (round-trip property)

### Requirement 18: Documentation Requirements

**User Story:** كمطور، أريد توثيق شامل للتغييرات، حتى يتمكن الفريق من فهم التحديثات.

#### Acceptance Criteria

1. THE documentation SHALL include before/after screenshots of all updated components
2. THE documentation SHALL list all modified files with descriptions
3. THE documentation SHALL document new CSS classes and their usage
4. THE documentation SHALL document new Vue.js methods and their parameters
5. THE documentation SHALL include code examples for common customizations
6. THE documentation SHALL document breaking changes and migration steps
7. THE documentation SHALL include troubleshooting guide for common issues
8. THE documentation SHALL be available in both English and Arabic

### Requirement 19: Performance Requirements

**User Story:** كمستخدم، أريد واجهة سريعة ومستجيبة، حتى لا تتأثر إنتاجيتي.

#### Acceptance Criteria

1. THE Sidebar SHALL render in less than 100ms
2. THE Hover animations SHALL maintain 60fps
3. THE Submenu SHALL appear within 50ms of hover
4. THE Mobile_Drawer SHALL open within 300ms
5. THE CSS bundle size SHALL not increase by more than 10%
6. THE JavaScript bundle size SHALL not increase by more than 5%
7. THE Admin_Panel SHALL pass Core Web Vitals thresholds (LCP < 2.5s, FID < 100ms, CLS < 0.1)

### Requirement 20: Browser Compatibility

**User Story:** كمستخدم، أريد أن تعمل الواجهة على جميع المتصفحات الحديثة، حتى أتمكن من استخدام المتصفح المفضل لدي.

#### Acceptance Criteria

1. THE Admin_Panel SHALL support Chrome 90+
2. THE Admin_Panel SHALL support Firefox 88+
3. THE Admin_Panel SHALL support Safari 14+
4. THE Admin_Panel SHALL support Edge 90+
5. THE Admin_Panel SHALL gracefully degrade on older browsers
6. THE Admin_Panel SHALL use CSS features with appropriate fallbacks
7. THE Admin_Panel SHALL test all features on supported browsers
