<?php

namespace Webkul\MedsdnApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Auth;
use Webkul\MedsdnApi\Dto\WalletSummaryOutput;
use Webkul\MedsdnApi\Exception\AuthenticationException;
use Webkul\Wallet\Services\WalletService;

class WalletSummaryProvider implements ProviderInterface
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $customer = $this->resolveCustomer();

        if (! $customer) {
            throw new AuthenticationException(trans('shop::app.customer.account.auth.unauthenticated'));
        }

        $wallet = $this->walletService->resolveCustomerWallet($customer->id);

        $output = new WalletSummaryOutput();
        $output->id = $wallet->id;
        $output->currency = $wallet->currency;
        $output->status = $wallet->status->value;
        $output->balance = (float) $wallet->balance;
        $output->availableBalance = (float) $wallet->available_balance;
        $output->heldBalance = (float) $wallet->held_balance;

        return $output;
    }

    protected function resolveCustomer(): ?object
    {
        return Auth::guard('sanctum')->user()
            ?: Auth::guard('api')->user()
            ?: Auth::guard('customer')->user();
    }
}
