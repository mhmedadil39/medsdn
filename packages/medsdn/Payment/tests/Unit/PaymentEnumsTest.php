<?php

namespace Webkul\Payment\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webkul\Payment\Enums\PaymentMethodCode;
use Webkul\Payment\Enums\PaymentPurpose;
use Webkul\Payment\Enums\PaymentStatus;

class PaymentEnumsTest extends TestCase
{
    public function test_terminal_statuses_are_marked_correctly(): void
    {
        $this->assertFalse(PaymentStatus::PENDING->isTerminal());
        $this->assertFalse(PaymentStatus::PENDING_REVIEW->isTerminal());
        $this->assertFalse(PaymentStatus::APPROVED->isTerminal());

        $this->assertTrue(PaymentStatus::PAID->isTerminal());
        $this->assertTrue(PaymentStatus::FAILED->isTerminal());
        $this->assertTrue(PaymentStatus::REJECTED->isTerminal());
        $this->assertTrue(PaymentStatus::CANCELLED->isTerminal());
        $this->assertTrue(PaymentStatus::REFUNDED->isTerminal());
    }

    public function test_payment_method_codes_are_stable(): void
    {
        $this->assertSame('banktransfer', PaymentMethodCode::BANK_TRANSFER->value);
        $this->assertSame('wallet', PaymentMethodCode::WALLET->value);
    }

    public function test_payment_purposes_are_stable(): void
    {
        $this->assertSame('wallet_topup', PaymentPurpose::WALLET_TOPUP->value);
        $this->assertSame('order_payment', PaymentPurpose::ORDER_PAYMENT->value);
    }
}
