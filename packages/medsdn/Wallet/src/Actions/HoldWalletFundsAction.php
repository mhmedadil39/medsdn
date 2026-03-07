<?php

namespace Webkul\Wallet\Actions;

use Webkul\Wallet\Models\Wallet;
use Webkul\Wallet\Services\WalletService;

class HoldWalletFundsAction
{
    public function __construct(protected WalletService $walletService) {}

    public function handle(Wallet $wallet, float $amount, string $entryKey, array $context = [])
    {
        return $this->walletService->hold($wallet, $amount, $entryKey, $context);
    }
}
