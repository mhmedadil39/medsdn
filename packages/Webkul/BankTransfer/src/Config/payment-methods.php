<?php

return [
    'banktransfer' => [
        'code'        => 'banktransfer',
        'title'       => 'Bank Transfer',
        'description' => 'Pay via bank transfer and upload payment proof',
        'class'       => 'Webkul\BankTransfer\Payment\BankTransfer',
        'active'      => true,
        'sort'        => 4,
    ],
];
