<?php

it('registers admin package menu items under menu admin', function () {
    $adminMenuKeys = collect(config('menu.admin'))->pluck('key');

    expect($adminMenuKeys)->toContain('sales.payments');
    expect($adminMenuKeys)->toContain('sales.bank_transfers');
    expect($adminMenuKeys)->toContain('customers.wallets');
})->skip(fn () => empty(config('menu.admin')), 'Admin menu config is not loaded.');

it('does not leak admin package menu items into the root menu array', function () {
    $rootMenuKeys = collect(config('menu'))
        ->filter(fn ($item) => is_array($item) && array_key_exists('key', $item))
        ->pluck('key');

    expect($rootMenuKeys)->not->toContain('sales.payments');
    expect($rootMenuKeys)->not->toContain('sales.bank_transfers');
    expect($rootMenuKeys)->not->toContain('customers.wallets');
});
