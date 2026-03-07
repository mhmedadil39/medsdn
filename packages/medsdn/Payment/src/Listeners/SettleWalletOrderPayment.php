<?php

namespace Webkul\Payment\Listeners;

use RuntimeException;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Payment\Actions\PayOrderWithWalletAction;
use Webkul\Payment\Enums\PaymentMethodCode;

class SettleWalletOrderPayment
{
    public function __construct(
        protected PayOrderWithWalletAction $payOrderWithWalletAction,
        protected CustomerRepository $customerRepository
    ) {}

    /**
     * Settle wallet-based orders immediately after order creation.
     */
    public function handle($order): void
    {
        if (! $order?->payment || $order->payment->method !== PaymentMethodCode::WALLET->value) {
            return;
        }

        if (! $order->customer_id) {
            throw new RuntimeException('Wallet payment requires an authenticated customer order.');
        }

        $customer = $this->customerRepository->find($order->customer_id);

        if (! $customer) {
            throw new RuntimeException('Customer not found for wallet payment.');
        }

        $this->payOrderWithWalletAction->handle($order, $customer, [
            'settlement_key' => 'order:'.$order->id.':wallet',
            'notes' => 'Order paid with wallet at checkout',
            'meta' => [
                'source' => 'checkout.order.save.after',
            ],
        ]);
    }
}
