<?php

use Illuminate\Support\Collection;
use Webkul\Core\Menu;
use Webkul\Core\Menu\MenuItem;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

/**
 * Create config for menu items.
 */
beforeEach(function () {
    config()->set('menu.admin', [
        [
            'key' => 'dashboard',
            'name' => 'admin::app.components.layouts.sidebar.dashboard',
            'route' => 'admin.dashboard.index',
            'sort' => 1,
            'icon' => 'icon-dashboard',
        ], [
            'key' => 'sales',
            'name' => 'admin::app.components.layouts.sidebar.sales',
            'route' => 'admin.sales.transactions.index',
            'sort' => 2,
            'icon' => 'icon-sales',
        ], [
            'key' => 'sales.orders',
            'name' => 'admin::app.components.layouts.sidebar.orders',
            'route' => 'admin.sales.orders.index',
            'sort' => 1,
            'icon' => '',
        ],
    ]);
});

it('should add and get menu items', function () {
    // Arrange.
    $menu = new Menu;

    foreach (config('menu.admin') as $menuItem) {
        $menu->addItem(new MenuItem(
            key: $menuItem['key'],
            name: trans($menuItem['name']),
            route: $menuItem['route'],
            sort: $menuItem['sort'],
            icon: $menuItem['icon'],
            children: collect([]),
        ));
    }

    $menuItems = $menu->getItems('admin');

    // Act and Assert.
    expect($menuItems->first()->key)->toBe('dashboard');

    expect($menuItems->first()->name)->toBe(trans('admin::app.components.layouts.sidebar.dashboard'));

    expect($menuItems->first()->route)->toBe('admin.dashboard.index');

    expect($menuItems->first()->sort)->toBe(1);

    expect($menuItems->first()->icon)->toBe('icon-dashboard');

    expect($menuItems->last()->key)->toBe('sales');
});

it('should preserve explicit parent route when menu item has children', function () {
    $menu = new Menu;

    $parent = new MenuItem(
        key: 'sales',
        name: 'Sales',
        route: 'admin.sales.transactions.index',
        sort: 1,
        icon: 'icon-sales',
        children: collect([
            new MenuItem(
                key: 'sales.orders',
                name: 'Orders',
                route: 'admin.sales.orders.index',
                sort: 1,
                icon: '',
                children: collect([]),
            ),
        ]),
    );

    $class = new ReflectionClass(Menu::class);
    $method = $class->getMethod('removeChildrenUnauthorizedMenuItem');
    $method->invokeArgs($menu, [&$parent]);

    expect($parent->route)->toBe('admin.sales.transactions.index');
});

it('should process sub menu items', function () {
    // Arrange.
    $menu = new Menu;

    $class = new ReflectionClass(Menu::class);

    $method = $class->getMethod('processSubMenuItems');

    $subMenuItems = $method->invoke($menu, config('menu.admin'));

    // Act and Assert.
    expect($subMenuItems)->toBeInstanceOf(Collection::class);

    expect($subMenuItems->count())->toBe(3);

    expect($subMenuItems->first())->toBeInstanceOf(MenuItem::class);

    expect($subMenuItems->first()->key)->toBe('dashboard');

    expect($subMenuItems->first()->name)->toBe(trans('admin::app.components.layouts.sidebar.dashboard'));

    expect($subMenuItems->first()->route)->toBe('admin.dashboard.index');

    expect($subMenuItems->first()->sort)->toBe(1);

    expect($subMenuItems->first()->icon)->toBe('icon-dashboard');

    expect($subMenuItems->last()->key)->toBe('sales');

    expect($subMenuItems->last()->name)->toBe(trans('admin::app.components.layouts.sidebar.sales'));
});

it('keeps a parent admin menu item when only a child permission is granted', function () {
    $role = Role::factory()->create([
        'permission_type' => 'custom',
        'permissions' => ['sales.orders'],
    ]);

    $admin = Admin::factory()->create([
        'role_id' => $role->id,
    ]);

    $this->actingAs($admin, 'admin');

    $menuItems = (new Menu)->getItems(Menu::ADMIN);

    expect($menuItems)->toHaveCount(1);
    expect($menuItems->first()->key)->toBe('sales');
    expect($menuItems->first()->route)->toBe('admin.sales.orders.index');
    expect($menuItems->first()->getChildren()->pluck('key')->all())->toBe(['sales.orders']);
});
