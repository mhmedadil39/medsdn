<?php

namespace Webkul\Wallet\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webkul\Wallet\Enums\WalletHoldStatus;
use Webkul\Wallet\Enums\WalletStatus;
use Webkul\Wallet\Enums\WalletTransactionDirection;
use Webkul\Wallet\Enums\WalletTransactionStatus;
use Webkul\Wallet\Enums\WalletTransactionType;

class WalletEnumsTest extends TestCase
{
    public function test_wallet_status_values_are_stable(): void
    {
        $this->assertSame('active', WalletStatus::ACTIVE->value);
        $this->assertSame('frozen', WalletStatus::FROZEN->value);
        $this->assertSame('disabled', WalletStatus::DISABLED->value);
    }

    public function test_wallet_transaction_types_cover_core_financial_operations(): void
    {
        $expected = [
            'topup',
            'debit',
            'refund',
            'hold',
            'release',
            'adjustment',
            'transfer',
        ];

        $actual = array_map(static fn (WalletTransactionType $type) => $type->value, WalletTransactionType::cases());

        $this->assertSame($expected, $actual);
    }

    public function test_wallet_transaction_direction_values_are_stable(): void
    {
        $this->assertSame('credit', WalletTransactionDirection::CREDIT->value);
        $this->assertSame('debit', WalletTransactionDirection::DEBIT->value);
    }

    public function test_wallet_transaction_status_values_are_stable(): void
    {
        $this->assertSame('pending', WalletTransactionStatus::PENDING->value);
        $this->assertSame('completed', WalletTransactionStatus::COMPLETED->value);
        $this->assertSame('failed', WalletTransactionStatus::FAILED->value);
        $this->assertSame('cancelled', WalletTransactionStatus::CANCELLED->value);
    }

    public function test_wallet_hold_status_values_are_stable(): void
    {
        $this->assertSame('active', WalletHoldStatus::ACTIVE->value);
        $this->assertSame('released', WalletHoldStatus::RELEASED->value);
        $this->assertSame('consumed', WalletHoldStatus::CONSUMED->value);
        $this->assertSame('cancelled', WalletHoldStatus::CANCELLED->value);
        $this->assertSame('expired', WalletHoldStatus::EXPIRED->value);
    }
}
