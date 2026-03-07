<?php

namespace Webkul\Wallet\Actions;

use Webkul\Wallet\Enums\WalletTransactionDirection;
use Webkul\Wallet\Enums\WalletTransactionStatus;
use Webkul\Wallet\Enums\WalletTransactionType;
use Webkul\Wallet\Models\Wallet;
use Webkul\Wallet\Models\WalletTransaction;

class CreateWalletTransactionAction
{
    public function findExisting(string $entryKey)
    {
        return WalletTransaction::query()->where('entry_key', $entryKey)->first();
    }

    public function handle(
        Wallet $wallet,
        WalletTransactionType $type,
        WalletTransactionDirection $direction,
        float $amount,
        float $balanceBefore,
        float $balanceAfter,
        string $entryKey,
        array $context = []
    ) {
        if ($existing = $this->findExisting($entryKey)) {
            return $existing;
        }

        return WalletTransaction::query()->create([
            'wallet_id' => $wallet->id,
            'customer_id' => $wallet->customer_id,
            'type' => $type,
            'direction' => $direction,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'status' => $context['status'] ?? WalletTransactionStatus::COMPLETED,
            'reference_type' => $context['reference_type'] ?? null,
            'reference_id' => $context['reference_id'] ?? null,
            'source' => $context['source'] ?? 'wallet',
            'description' => $context['description'] ?? null,
            'meta' => $context['meta'] ?? [],
            'created_by_type' => $context['created_by_type'] ?? null,
            'created_by_id' => $context['created_by_id'] ?? null,
            'entry_key' => $entryKey,
        ]);
    }
}
