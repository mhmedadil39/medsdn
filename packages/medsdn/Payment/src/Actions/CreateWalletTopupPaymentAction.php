<?php

namespace Webkul\Payment\Actions;

use Webkul\Customer\Models\Customer;
use Webkul\Payment\Enums\PaymentMethodCode;
use Webkul\Payment\Services\PaymentService;

class CreateWalletTopupPaymentAction
{
    public function __construct(protected PaymentService $paymentService) {}

    public function handle(Customer $customer, float $amount, array $attributes = [])
    {
        return $this->paymentService->createWalletTopup($customer, $amount, PaymentMethodCode::BANK_TRANSFER, $attributes);
    }
}
