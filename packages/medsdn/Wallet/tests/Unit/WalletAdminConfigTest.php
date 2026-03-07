<?php

it('registers wallet admin menu entries for customers and sales', function () {
    $adminMenuKeys = collect(config('menu.admin'))->pluck('key');

    expect($adminMenuKeys)->toContain('customers.wallets');
    expect($adminMenuKeys)->toContain('sales.wallet_transactions');
});

it('registers granular wallet acl entries for customers and sales access', function () {
    $aclKeys = collect(config('acl'))->pluck('key');

    expect($aclKeys)->toContain('customers.wallets');
    expect($aclKeys)->toContain('customers.wallets.view');
    expect($aclKeys)->toContain('customers.wallets.adjust');
    expect($aclKeys)->toContain('sales.wallet_transactions');
});
