<?php

namespace Webkul\Payment\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PENDING_REVIEW = 'pending_review';
    case APPROVED = 'approved';
    case PAID = 'paid';
    case FAILED = 'failed';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::PAID,
            self::FAILED,
            self::REJECTED,
            self::CANCELLED,
            self::REFUNDED,
        ], true);
    }
}
