<?php

namespace Webkul\Payment\Actions;

use Webkul\Payment\Models\Payment;
use Webkul\Payment\Services\PaymentService;

class ApproveManualPaymentAction
{
    public function __construct(protected PaymentService $paymentService) {}

    public function handle(Payment $payment, int $adminId, ?string $adminNotes = null)
    {
        return $this->paymentService->approveManual($payment, $adminId, $adminNotes);
    }
}
