<?php

namespace Webkul\Payment\Enums;

enum PaymentMethodCode: string
{
    case BANK_TRANSFER = 'banktransfer';
    case WALLET = 'wallet';
    case CASH_ON_DELIVERY = 'cashondelivery';
    case MONEY_TRANSFER = 'moneytransfer';
}
