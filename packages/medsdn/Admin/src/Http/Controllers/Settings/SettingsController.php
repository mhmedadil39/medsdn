<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Webkul\Admin\Http\Controllers\Controller;

class SettingsController extends Controller
{
    public function index()
    {
        $sections = [
            ['title' => 'Locales', 'route' => 'admin.settings.locales.index'],
            ['title' => 'Currencies', 'route' => 'admin.settings.currencies.index'],
            ['title' => 'Exchange Rates', 'route' => 'admin.settings.exchange_rates.index'],
            ['title' => 'Inventory Sources', 'route' => 'admin.settings.inventory_sources.index'],
            ['title' => 'Channels', 'route' => 'admin.settings.channels.index'],
            ['title' => 'Users', 'route' => 'admin.settings.users.index'],
            ['title' => 'Roles', 'route' => 'admin.settings.roles.index'],
            ['title' => 'Themes', 'route' => 'admin.settings.themes.index'],
            ['title' => 'Tax Categories', 'route' => 'admin.settings.taxes.categories.index'],
            ['title' => 'Tax Rates', 'route' => 'admin.settings.taxes.rates.index'],
            ['title' => 'Data Transfer', 'route' => 'admin.settings.data_transfer.imports.index'],
        ];

        return view('admin::settings.index', compact('sections'));
    }
}
