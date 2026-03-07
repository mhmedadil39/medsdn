<?php

namespace Webkul\Wallet\Actions;

use Webkul\Wallet\Models\WalletHold;
use Webkul\Wallet\Services\WalletService;

class ReleaseWalletHoldAction
{
    public function __construct(protected WalletService $walletService) {}

    public function handle(WalletHold $hold, string $entryKey, array $context = [])
    {
        return $this->walletService->releaseHold($hold, $entryKey, $context);
    }
}
