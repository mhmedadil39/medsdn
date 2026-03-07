<?php

namespace Webkul\Payment\Actions;

use Webkul\Payment\Models\Payment;
use Webkul\Payment\Services\PaymentService;

class RejectManualPaymentAction
{
    public function __construct(protected PaymentService $paymentService) {}

    public function handle(Payment $payment, int $adminId, string $reason, ?string $adminNotes = null)
    {
        return $this->paymentService->rejectManual($payment, $adminId, $reason, $adminNotes);
    }
}
