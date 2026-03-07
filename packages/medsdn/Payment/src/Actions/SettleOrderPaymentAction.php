<?php

namespace Webkul\Payment\Actions;

use Webkul\Payment\Models\Payment;
use Webkul\Payment\Services\PaymentService;

class SettleOrderPaymentAction
{
    public function __construct(protected PaymentService $paymentService) {}

    public function handle(Payment $payment)
    {
        return $this->paymentService->settleOrderPayment($payment);
    }
}
