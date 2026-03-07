<?php

use function Pest\Laravel\get;

it('renders the quick creation component with medsdn shortcuts', function () {
    $this->loginAsAdmin();

    $this->view('admin::components.layouts.header.quick-creation')
        ->assertSee('icon-add', false)
        ->assertSee(route('admin.catalog.products.index'), false)
        ->assertSee('<img', false)
        ->assertSee('alt="' . trans('admin::app.catalog.products.index.title') . '"', false)
        ->assertSee(route('admin.catalog.categories.create'), false)
        ->assertSee('alt="' . trans('admin::app.catalog.categories.index.title') . '"', false)
        ->assertSee(route('admin.catalog.attributes.create'), false)
        ->assertSee('alt="' . trans('admin::app.catalog.attributes.index.title') . '"', false)
        ->assertSee(route('admin.catalog.families.create'), false)
        ->assertSee('alt="' . trans('admin::app.catalog.families.index.title') . '"', false)
        ->assertSee(route('admin.cms.create'), false)
        ->assertSee('alt="' . trans('admin::app.components.layouts.sidebar.cms') . '"', false)
        ->assertSee(route('admin.marketing.promotions.cart_rules.create'), false)
        ->assertSee('alt="' . trans('admin::app.marketing.promotions.index.cart-rule-title') . '"', false)
        ->assertSee(route('admin.settings.inventory_sources.create'), false)
        ->assertSee('alt="' . trans('admin::app.settings.inventory-sources.index.title') . '"', false)
        ->assertSee(route('admin.settings.roles.create'), false)
        ->assertSee('alt="' . trans('admin::app.settings.roles.index.title') . '"', false)
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

it('renders the redesigned header while preserving admin utilities', function () {
    $this->loginAsAdmin();

    get(route('admin.dashboard.index'))
        ->assertOk()
        ->assertSee(route('shop.home.index'), false)
        ->assertSee('v-notifications-template', false)
        ->assertSee('data-role="header-quick-create"', false);
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
        ->assertSee('data-sidebar-icon="dashboard"', false)
        ->assertSee('data-sidebar-icon="sales"', false)
        ->assertSee('data-sidebar-icon="catalog"', false)
        ->assertSee('data-sidebar-icon="settings"', false)
        ->assertSee('data-sidebar-icon="configuration"', false)
        ->assertSee('v-sidebar-drawer-template', false)
        ->assertSee('v-mobile-mega-search-template', false)
        ->assertSee('icon-add', false);
});
