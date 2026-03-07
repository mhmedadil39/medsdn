<?php

namespace Webkul\Wallet\Enums;

enum WalletTransactionType: string
{
    case TOPUP = 'topup';
    case DEBIT = 'debit';
    case REFUND = 'refund';
    case HOLD = 'hold';
    case RELEASE = 'release';
    case ADJUSTMENT = 'adjustment';
    case TRANSFER = 'transfer';
}
