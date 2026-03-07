<?php

namespace Webkul\Wallet\Actions;

use Webkul\Wallet\Enums\WalletTransactionType;
use Webkul\Wallet\Models\Wallet;
use Webkul\Wallet\Services\WalletService;

class DebitWalletAction
{
    public function __construct(protected WalletService $walletService) {}

    public function handle(Wallet $wallet, float $amount, string $entryKey, array $context = [])
    {
        return $this->walletService->debit($wallet, $amount, $context['type'] ?? WalletTransactionType::DEBIT, $entryKey, $context);
    }
}
