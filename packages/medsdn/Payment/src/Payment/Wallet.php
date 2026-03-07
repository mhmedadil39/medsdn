<?php

namespace Webkul\Payment\Payment;

use Illuminate\Support\Facades\Auth;
use Webkul\Payment\Enums\PaymentMethodCode;
use Webkul\Wallet\Services\WalletService;

class Wallet extends Payment
{
    protected $code = 'wallet';

    public function __construct(protected WalletService $walletService) {}

    public function getRedirectUrl()
    {
        return null;
    }

    public function isAvailable()
    {
        if (! parent::isAvailable()) {
            return false;
        }

        $customerId = $this->resolveCustomerId();

        if (! $customerId) {
            return false;
        }

        $cart = $this->getCart();

        if (! $cart || ! $cart->base_grand_total) {
            return false;
        }

        $wallet = $this->walletService->resolveCustomerWallet(
            customerId: $customerId,
            currency: $cart->base_currency_code
        );

        return $wallet->isActive()
            && $wallet->currency === $cart->base_currency_code
            && (float) $wallet->available_balance >= (float) $cart->base_grand_total;
    }

    public function getAdditionalDetails()
    {
        $customerId = $this->resolveCustomerId();

        if (! $customerId) {
            return [];
        }

        $cart = $this->getCart();

        $wallet = $this->walletService->resolveCustomerWallet(
            $customerId,
            $cart?->base_currency_code
        );

        return [
            'title' => 'Wallet balance',
            'value' => core()->formatBasePrice((float) $wallet->available_balance),
            'meta' => [
                'payment_method' => PaymentMethodCode::WALLET->value,
                'currency' => $wallet->currency,
                'available_balance' => (float) $wallet->available_balance,
                'balance' => (float) $wallet->balance,
                'held_balance' => (float) $wallet->held_balance,
            ],
        ];
    }

    protected function resolveCustomerId(): ?int
    {
        if (Auth::guard('customer')->check()) {
            return (int) Auth::guard('customer')->id();
        }

        if (Auth::guard('api')->check()) {
            return (int) Auth::guard('api')->id();
        }

        $cart = $this->getCart();

        return $cart?->customer_id ? (int) $cart->customer_id : null;
    }
}
