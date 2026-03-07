<?php

use function Pest\Laravel\get;

it('stores the streamline shell icon source files', function () {
    $iconPaths = [
        'visit-shop.svg',
        'sidebar-dashboard.svg',
        'sidebar-sales.svg',
        'sidebar-catalog.svg',
        'sidebar-customers.svg',
        'sidebar-cms.svg',
        'sidebar-marketing.svg',
        'sidebar-reporting.svg',
        'sidebar-settings.svg',
        'sidebar-configuration.svg',
        'quick-product.svg',
        'quick-category.svg',
        'quick-attribute.svg',
        'quick-family.svg',
        'quick-cms.svg',
        'quick-cart-rule.svg',
        'quick-inventory-source.svg',
        'quick-role.svg',
    ];

    foreach ($iconPaths as $iconPath) {
        expect(base_path('packages/medsdn/Admin/src/Resources/assets/images/streamline-plump-color/' . $iconPath))
            ->toBeFile();
    }
});

it('renders the quick creation component with medsdn shortcuts', function () {
    $this->loginAsAdmin();

    $this->view('admin::components.layouts.header.quick-creation')
        ->assertSee('icon-add', false)
        ->assertSee('data-role="header-quick-create-grid"', false)
        ->assertSee('data-shell-icon="quick-product"', false)
        ->assertSee(route('admin.catalog.products.index'), false)
        ->assertSee('streamline-plump-color/quick-product.svg', false)
        ->assertSee(route('admin.catalog.categories.create'), false)
        ->assertSee('data-shell-icon="quick-category"', false)
        ->assertSee('streamline-plump-color/quick-category.svg', false)
        ->assertSee(route('admin.catalog.attributes.create'), false)
        ->assertSee('data-shell-icon="quick-attribute"', false)
        ->assertSee('streamline-plump-color/quick-attribute.svg', false)
        ->assertSee(route('admin.catalog.families.create'), false)
        ->assertSee('data-shell-icon="quick-family"', false)
        ->assertSee('streamline-plump-color/quick-family.svg', false)
        ->assertSee(route('admin.cms.create'), false)
        ->assertSee('data-shell-icon="quick-cms"', false)
        ->assertSee('streamline-plump-color/quick-cms.svg', false)
        ->assertSee(route('admin.marketing.promotions.cart_rules.create'), false)
        ->assertSee('data-shell-icon="quick-cart-rule"', false)
        ->assertSee('streamline-plump-color/quick-cart-rule.svg', false)
        ->assertSee(route('admin.settings.inventory_sources.create'), false)
        ->assertSee('data-shell-icon="quick-inventory-source"', false)
        ->assertSee('streamline-plump-color/quick-inventory-source.svg', false)
        ->assertSee(route('admin.settings.roles.create'), false)
        ->assertSee('data-shell-icon="quick-role"', false)
        ->assertSee('streamline-plump-color/quick-role.svg', false)
        ->assertDontSee('icon-folder', false)
        ->assertDontSee('icon-attribute', false)
        ->assertDontSee('icon-folder-block', false)
        ->assertDontSee('icon-discount', false)
        ->assertDontSee('icon-location', false);
});

it('renders the mobile mega search component with medsdn search endpoints', function () {
    $this->loginAsAdmin();

    $this->view('admin::components.layouts.header.mobile.mega-search')
        ->assertSee('v-mobile-mega-search-template', false)
        ->assertSee(route('admin.catalog.products.search'), false)
        ->assertSee(route('admin.sales.orders.search'), false)
        ->assertSee(route('admin.catalog.categories.search'), false)
        ->assertSee(route('admin.customers.customers.search'), false);
});

it('renders the mobile sidebar drawer with accordion navigation', function () {
    $this->loginAsAdmin();

    get(route('admin.dashboard.index'))
        ->assertOk()
        ->assertSee('v-sidebar-drawer-template', false)
        ->assertSee('data-menu-key=', false)
        ->assertSee('toggleMenu(', false)
        ->assertSee('submenu', false);
});

it('renders the desktop sidebar with streamline shell icon mappings', function () {
    $this->loginAsAdmin();

    $this->view('admin::components.layouts.sidebar.desktop.index')
        ->assertSee('data-sidebar-icon="dashboard"', false)
        ->assertSee('data-shell-icon="sidebar-dashboard"', false)
        ->assertSee('streamline-plump-color/sidebar-dashboard.svg', false)
        ->assertSee('data-sidebar-icon="sales"', false)
        ->assertSee('data-shell-icon="sidebar-sales"', false)
        ->assertSee('streamline-plump-color/sidebar-sales.svg', false)
        ->assertSee('data-sidebar-icon="catalog"', false)
        ->assertSee('data-shell-icon="sidebar-catalog"', false)
        ->assertSee('streamline-plump-color/sidebar-catalog.svg', false)
        ->assertSee('data-sidebar-icon="customers"', false)
        ->assertSee('data-shell-icon="sidebar-customers"', false)
        ->assertSee('streamline-plump-color/sidebar-customers.svg', false)
        ->assertSee('data-sidebar-icon="cms"', false)
        ->assertSee('data-shell-icon="sidebar-cms"', false)
        ->assertSee('streamline-plump-color/sidebar-cms.svg', false)
        ->assertSee('data-sidebar-icon="marketing"', false)
        ->assertSee('data-shell-icon="sidebar-marketing"', false)
        ->assertSee('streamline-plump-color/sidebar-marketing.svg', false)
        ->assertSee('data-sidebar-icon="reporting"', false)
        ->assertSee('data-shell-icon="sidebar-reporting"', false)
        ->assertSee('streamline-plump-color/sidebar-reporting.svg', false)
        ->assertSee('data-sidebar-icon="settings"', false)
        ->assertSee('data-shell-icon="sidebar-settings"', false)
        ->assertSee('streamline-plump-color/sidebar-settings.svg', false)
        ->assertSee('data-sidebar-icon="configuration"', false)
        ->assertSee('data-shell-icon="sidebar-configuration"', false)
        ->assertSee('streamline-plump-color/sidebar-configuration.svg', false);
});

it('renders the mobile sidebar with streamline shell icon mappings', function () {
    $this->loginAsAdmin();

    $this->view('admin::components.layouts.sidebar.mobile.index')
        ->assertSee('data-sidebar-icon="dashboard"', false)
        ->assertSee('data-mobile-shell-icon="sidebar-dashboard"', false)
        ->assertSee('streamline-plump-color/sidebar-dashboard.svg', false)
        ->assertSee('data-sidebar-icon="sales"', false)
        ->assertSee('data-mobile-shell-icon="sidebar-sales"', false)
        ->assertSee('streamline-plump-color/sidebar-sales.svg', false)
        ->assertSee('data-sidebar-icon="catalog"', false)
        ->assertSee('data-mobile-shell-icon="sidebar-catalog"', false)
        ->assertSee('streamline-plump-color/sidebar-catalog.svg', false)
        ->assertSee('data-sidebar-icon="customers"', false)
        ->assertSee('data-mobile-shell-icon="sidebar-customers"', false)
        ->assertSee('streamline-plump-color/sidebar-customers.svg', false)
        ->assertSee('data-sidebar-icon="cms"', false)
        ->assertSee('data-mobile-shell-icon="sidebar-cms"', false)
        ->assertSee('streamline-plump-color/sidebar-cms.svg', false)
        ->assertSee('data-sidebar-icon="marketing"', false)
        ->assertSee('data-mobile-shell-icon="sidebar-marketing"', false)
        ->assertSee('streamline-plump-color/sidebar-marketing.svg', false)
        ->assertSee('data-sidebar-icon="reporting"', false)
        ->assertSee('data-mobile-shell-icon="sidebar-reporting"', false)
        ->assertSee('streamline-plump-color/sidebar-reporting.svg', false)
        ->assertSee('data-sidebar-icon="settings"', false)
        ->assertSee('data-mobile-shell-icon="sidebar-settings"', false)
        ->assertSee('streamline-plump-color/sidebar-settings.svg', false)
        ->assertSee('data-sidebar-icon="configuration"', false)
        ->assertSee('data-mobile-shell-icon="sidebar-configuration"', false)
        ->assertSee('streamline-plump-color/sidebar-configuration.svg', false);
});

it('renders the redesigned header while preserving admin utilities', function () {
    $this->loginAsAdmin();

    get(route('admin.dashboard.index'))
        ->assertOk()
        ->assertSee(route('shop.home.index'), false)
        ->assertSee('v-notifications-template', false)
        ->assertSee('data-role="header-quick-create"', false)
        ->assertSee('data-role="shell-brand-lockup"', false)
        ->assertSee('data-shell-icon="visit-shop"', false)
        ->assertSee('streamline-plump-color/visit-shop.svg', false);
});

it('renders admin branding with medsdn identity', function () {
    $this->loginAsAdmin();

    get(route('admin.dashboard.index'))
        ->assertOk()
        ->assertSee('content="MedSDN"', false)
        ->assertSee('MedSDN', false)
        ->assertDontSee('bagisto.png', false)
        ->assertDontSee('>Bagisto<', false)
        ->assertDontSee('>Webkul<', false);
});

it('renders the admin dashboard with the redesigned collapsed shell and mobile controls', function () {
    $this->loginAsAdmin();

    get(route('admin.dashboard.index'))
        ->assertOk()
        ->assertSee('group/container sidebar-collapsed flex gap-4', false)
        ->assertSee('v-sidebar-drawer-template', false)
        ->assertSee('v-mobile-mega-search-template', false)
        ->assertSee('icon-add', false);
});
