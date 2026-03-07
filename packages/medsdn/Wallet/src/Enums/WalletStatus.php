<?php

namespace Webkul\Wallet\Enums;

enum WalletStatus: string
{
    case ACTIVE = 'active';
    case FROZEN = 'frozen';
    case DISABLED = 'disabled';
}
