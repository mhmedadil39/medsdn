<?php

return [
    [
        'key'   => 'customers.wallets',
        'name'  => 'Wallets',
        'route' => 'admin.customers.wallets.index',
        'sort'  => 6,
    ],
    [
        'key'   => 'customers.wallets.view',
        'name'  => 'View Wallets',
        'route' => 'admin.customers.wallets.view',
        'sort'  => 1,
    ],
    [
        'key'   => 'customers.wallets.adjust',
        'name'  => 'Adjust Wallets',
        'route' => 'admin.customers.wallets.adjust',
        'sort'  => 2,
    ],
    [
        'key'   => 'sales.wallet_transactions',
        'name'  => 'Wallet Transactions',
        'route' => 'admin.sales.wallet_transactions.index',
        'sort'  => 8,
    ],
];
