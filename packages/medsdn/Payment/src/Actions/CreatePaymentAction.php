<?php

namespace Webkul\Payment\Actions;

use Webkul\Payment\Services\PaymentService;

class CreatePaymentAction
{
    public function __construct(protected PaymentService $paymentService) {}

    public function handle(array $data)
    {
        return $this->paymentService->create($data);
    }
}
