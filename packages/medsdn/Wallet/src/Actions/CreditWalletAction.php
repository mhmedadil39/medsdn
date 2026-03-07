<?php

namespace Webkul\Wallet\Actions;

use Webkul\Wallet\Enums\WalletTransactionType;
use Webkul\Wallet\Models\Wallet;
use Webkul\Wallet\Services\WalletService;

class CreditWalletAction
{
    public function __construct(protected WalletService $walletService) {}

    public function handle(Wallet $wallet, float $amount, string $entryKey, array $context = [])
    {
        return $this->walletService->credit($wallet, $amount, $context['type'] ?? WalletTransactionType::TOPUP, $entryKey, $context);
    }
}
