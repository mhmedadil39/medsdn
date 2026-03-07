<?php

namespace Webkul\Payment\Enums;

enum PaymentPurpose: string
{
    case WALLET_TOPUP = 'wallet_topup';
    case ORDER_PAYMENT = 'order_payment';
}
