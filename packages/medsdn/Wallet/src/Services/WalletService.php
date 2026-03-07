<?php

namespace Webkul\Wallet\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;
use Webkul\Wallet\Actions\CreateWalletTransactionAction;
use Webkul\Wallet\Enums\WalletHoldStatus;
use Webkul\Wallet\Enums\WalletStatus;
use Webkul\Wallet\Enums\WalletTransactionDirection;
use Webkul\Wallet\Enums\WalletTransactionStatus;
use Webkul\Wallet\Enums\WalletTransactionType;
use Webkul\Wallet\Models\Wallet;
use Webkul\Wallet\Models\WalletHold;

class WalletService
{
    public function __construct(
        protected CreateWalletTransactionAction $createWalletTransactionAction
    ) {}

    public function resolveCustomerWallet(int $customerId, ?string $currency = null): Wallet
    {
        return Wallet::query()->firstOrCreate(
            ['customer_id' => $customerId],
            [
                'currency' => $currency ?: core()->getBaseCurrencyCode(),
                'balance' => 0,
                'available_balance' => 0,
                'held_balance' => 0,
                'status' => WalletStatus::ACTIVE,
            ]
        );
    }

    public function credit(
        Wallet $wallet,
        float $amount,
        WalletTransactionType $type,
        string $entryKey,
        array $context = []
    ) {
        return DB::transaction(function () use ($wallet, $amount, $type, $entryKey, $context) {
            $this->assertAmount($amount);

            $existing = $this->createWalletTransactionAction->findExisting($entryKey);

            if ($existing) {
                return $existing;
            }

            $wallet = Wallet::query()->lockForUpdate()->findOrFail($wallet->id);
            $this->assertWalletIsActive($wallet);

            $before = (float) $wallet->balance;
            $after = round($before + $amount, 4);

            $wallet->forceFill([
                'balance' => $after,
                'available_balance' => round((float) $wallet->available_balance + $amount, 4),
            ])->save();

            return $this->createWalletTransactionAction->handle(
                wallet: $wallet,
                type: $type,
                direction: WalletTransactionDirection::CREDIT,
                amount: $amount,
                balanceBefore: $before,
                balanceAfter: $after,
                entryKey: $entryKey,
                context: $context
            );
        });
    }

    public function debit(
        Wallet $wallet,
        float $amount,
        WalletTransactionType $type,
        string $entryKey,
        array $context = []
    ) {
        return DB::transaction(function () use ($wallet, $amount, $type, $entryKey, $context) {
            $this->assertAmount($amount);

            $existing = $this->createWalletTransactionAction->findExisting($entryKey);

            if ($existing) {
                return $existing;
            }

            $wallet = Wallet::query()->lockForUpdate()->findOrFail($wallet->id);
            $this->assertWalletIsActive($wallet);

            if ((float) $wallet->available_balance < $amount) {
                throw new RuntimeException('Insufficient wallet balance.');
            }

            $before = (float) $wallet->balance;
            $after = round($before - $amount, 4);

            $wallet->forceFill([
                'balance' => $after,
                'available_balance' => round((float) $wallet->available_balance - $amount, 4),
            ])->save();

            return $this->createWalletTransactionAction->handle(
                wallet: $wallet,
                type: $type,
                direction: WalletTransactionDirection::DEBIT,
                amount: $amount,
                balanceBefore: $before,
                balanceAfter: $after,
                entryKey: $entryKey,
                context: $context
            );
        });
    }

    public function hold(
        Wallet $wallet,
        float $amount,
        string $entryKey,
        array $context = []
    ): WalletHold {
        return DB::transaction(function () use ($wallet, $amount, $entryKey, $context) {
            $this->assertAmount($amount);

            $wallet = Wallet::query()->lockForUpdate()->findOrFail($wallet->id);
            $this->assertWalletIsActive($wallet);

            if ((float) $wallet->available_balance < $amount) {
                throw new RuntimeException('Insufficient wallet balance to hold.');
            }

            if (! empty($context['reference_type']) && ! empty($context['reference_id'])) {
                $hold = WalletHold::query()
                    ->where('reference_type', $context['reference_type'])
                    ->where('reference_id', $context['reference_id'])
                    ->where('status', WalletHoldStatus::ACTIVE);

                if ($existing = $hold->first()) {
                    return $existing;
                }
            }

            $wallet->forceFill([
                'available_balance' => round((float) $wallet->available_balance - $amount, 4),
                'held_balance' => round((float) $wallet->held_balance + $amount, 4),
            ])->save();

            return WalletHold::query()->create([
                'wallet_id' => $wallet->id,
                'customer_id' => $wallet->customer_id,
                'reference_type' => $context['reference_type'] ?? null,
                'reference_id' => $context['reference_id'] ?? null,
                'amount' => $amount,
                'status' => WalletHoldStatus::ACTIVE,
                'expires_at' => $context['expires_at'] ?? null,
                'meta' => array_merge($context['meta'] ?? [], ['entry_key' => $entryKey]),
            ]);
        });
    }

    public function releaseHold(
        WalletHold $hold,
        string $entryKey,
        array $context = []
    ): WalletHold {
        return DB::transaction(function () use ($hold, $entryKey, $context) {
            $hold = WalletHold::query()->lockForUpdate()->findOrFail($hold->id);

            if ($hold->status !== WalletHoldStatus::ACTIVE) {
                return $hold;
            }

            $wallet = Wallet::query()->lockForUpdate()->findOrFail($hold->wallet_id);

            $wallet->forceFill([
                'available_balance' => round((float) $wallet->available_balance + (float) $hold->amount, 4),
                'held_balance' => round((float) $wallet->held_balance - (float) $hold->amount, 4),
            ])->save();

            $hold->forceFill([
                'status' => $context['status'] ?? WalletHoldStatus::RELEASED,
                'released_at' => now(),
                'meta' => array_merge($hold->meta ?? [], $context['meta'] ?? [], ['entry_key' => $entryKey]),
            ])->save();

            return $hold;
        });
    }

    public function adjust(
        Wallet $wallet,
        float $amount,
        string $entryKey,
        array $context = []
    ) {
        return $amount >= 0
            ? $this->credit($wallet, $amount, WalletTransactionType::ADJUSTMENT, $entryKey, $context)
            : $this->debit($wallet, abs($amount), WalletTransactionType::ADJUSTMENT, $entryKey, $context);
    }

    protected function assertWalletIsActive(Wallet $wallet): void
    {
        if (! $wallet->isActive()) {
            throw new RuntimeException('Wallet is not active.');
        }
    }

    protected function assertAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be greater than zero.');
        }
    }
}
