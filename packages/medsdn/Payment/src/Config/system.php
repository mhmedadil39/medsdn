<?php

return [
    [
        'key'    => 'sales.payment_methods.wallet',
        'name'   => 'Wallet',
        'info'   => 'Configure wallet payment availability and labels.',
        'sort'   => 6,
        'fields' => [
            [
                'name'          => 'active',
                'title'         => 'Enable wallet payment',
                'type'          => 'boolean',
                'default'       => true,
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'          => 'title',
                'title'         => 'Wallet title',
                'type'          => 'text',
                'default'       => 'Wallet',
                'channel_based' => true,
                'locale_based'  => true,
            ],
            [
                'name'          => 'description',
                'title'         => 'Wallet description',
                'type'          => 'textarea',
                'default'       => 'Pay using your wallet balance',
                'channel_based' => true,
                'locale_based'  => true,
            ],
            [
                'name'          => 'sort',
                'title'         => 'Sort order',
                'type'          => 'text',
                'default'       => 3,
                'channel_based' => false,
                'locale_based'  => false,
            ],
        ],
    ],
];
