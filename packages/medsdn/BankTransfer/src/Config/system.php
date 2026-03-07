<?php

return [
    [
        'key'    => 'sales.payment_methods.banktransfer',
        'name'   => 'banktransfer::app.admin.system.title',
        'info'   => 'banktransfer::app.admin.system.info',
        'sort'   => 4,
        'fields' => [
            [
                'name'          => 'active',
                'title'         => 'banktransfer::app.admin.system.active',
                'type'          => 'boolean',
                'default'       => true,
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'          => 'title',
                'title'         => 'banktransfer::app.admin.system.title-field',
                'type'          => 'text',
                'default'       => 'Bank Transfer',
                'channel_based' => true,
                'locale_based'  => true,
            ],
            [
                'name'          => 'description',
                'title'         => 'banktransfer::app.admin.system.description',
                'type'          => 'textarea',
                'channel_based' => true,
                'locale_based'  => true,
            ],
            [
                'name'          => 'sort',
                'title'         => 'banktransfer::app.admin.system.sort-order',
                'type'          => 'text',
                'default'       => 4,
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'          => 'instructions',
                'title'         => 'banktransfer::app.admin.system.instructions',
                'type'          => 'textarea',
                'default'       => 'Please transfer the order amount to one of the bank accounts below and upload your payment proof.',
                'channel_based' => true,
                'locale_based'  => true,
            ],
            
            // Bank Account 1
            [
                'name'          => 'bank_1_name',
                'title'         => 'banktransfer::app.admin.system.bank-1-name',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'          => 'bank_1_branch',
                'title'         => 'banktransfer::app.admin.system.bank-1-branch',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'          => 'bank_1_account_holder',
                'title'         => 'banktransfer::app.admin.system.bank-1-holder',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'          => 'bank_1_account_number',
                'title'         => 'banktransfer::app.admin.system.bank-1-number',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'          => 'bank_1_iban',
                'title'         => 'banktransfer::app.admin.system.bank-1-iban',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => false,
            ],
            
            // Bank Account 2
            [
                'name'          => 'bank_2_name',
                'title'         => 'banktransfer::app.admin.system.bank-2-name',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'          => 'bank_2_branch',
                'title'         => 'banktransfer::app.admin.system.bank-2-branch',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'          => 'bank_2_account_holder',
                'title'         => 'banktransfer::app.admin.system.bank-2-holder',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'          => 'bank_2_account_number',
                'title'         => 'banktransfer::app.admin.system.bank-2-number',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'          => 'bank_2_iban',
                'title'         => 'banktransfer::app.admin.system.bank-2-iban',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => false,
            ],
            
            // Bank Account 3
            [
                'name'          => 'bank_3_name',
                'title'         => 'banktransfer::app.admin.system.bank-3-name',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'          => 'bank_3_branch',
                'title'         => 'banktransfer::app.admin.system.bank-3-branch',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'          => 'bank_3_account_holder',
                'title'         => 'banktransfer::app.admin.system.bank-3-holder',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'          => 'bank_3_account_number',
                'title'         => 'banktransfer::app.admin.system.bank-3-number',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'          => 'bank_3_iban',
                'title'         => 'banktransfer::app.admin.system.bank-3-iban',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => false,
            ],
        ],
    ],
];
