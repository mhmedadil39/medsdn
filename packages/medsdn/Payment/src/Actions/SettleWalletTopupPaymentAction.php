<?php

namespace Webkul\Payment\Actions;

use Webkul\Payment\Models\Payment;
use Webkul\Payment\Services\PaymentService;

class SettleWalletTopupPaymentAction
{
    public function __construct(protected PaymentService $paymentService) {}

    public function handle(Payment $payment)
    {
        return $this->paymentService->settleWalletTopup($payment);
    }
}
