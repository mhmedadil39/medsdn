<?php

namespace Webkul\Payment\Actions;

use Webkul\Customer\Models\Customer;
use Webkul\Payment\Services\PaymentService;
use Webkul\Sales\Models\Order;

class PayOrderWithWalletAction
{
    public function __construct(protected PaymentService $paymentService) {}

    public function handle(Order $order, Customer $customer, array $attributes = [])
    {
        return $this->paymentService->payOrderWithWallet($order, $customer, $attributes);
    }
}
