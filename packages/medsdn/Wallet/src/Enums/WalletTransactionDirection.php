<?php

namespace Webkul\Wallet\Enums;

enum WalletTransactionDirection: string
{
    case CREDIT = 'credit';
    case DEBIT = 'debit';
}
