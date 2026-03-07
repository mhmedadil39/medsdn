<?php

use function Pest\Laravel\get;

it('should return the settings landing page', function () {
    $this->loginAsAdmin();

    get(route('admin.settings.index'))
        ->assertOk()
        ->assertSeeText('Settings')
        ->assertSee(route('admin.settings.locales.index'), false)
        ->assertSee(route('admin.settings.users.index'), false)
        ->assertSee(route('admin.settings.roles.index'), false);
});

it('renders the settings parent menu link to the settings landing page', function () {
    $this->loginAsAdmin();

    $this->view('admin::components.layouts.sidebar.desktop.index')
        ->assertSee(route('admin.settings.index'), false);
});
