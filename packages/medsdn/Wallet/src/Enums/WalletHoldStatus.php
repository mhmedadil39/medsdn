<?php

namespace Webkul\Wallet\Enums;

enum WalletHoldStatus: string
{
    case ACTIVE = 'active';
    case RELEASED = 'released';
    case CONSUMED = 'consumed';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
}
