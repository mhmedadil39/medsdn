<?php

namespace Webkul\Payment\Actions;

use Webkul\Payment\Enums\PaymentMethodCode;
use Webkul\Payment\Enums\PaymentStatus;
use Webkul\Payment\Services\PaymentService;
use Webkul\Sales\Models\Order;

class CreateOrderPaymentAction
{
    public function __construct(protected PaymentService $paymentService) {}

    public function handle(Order $order, PaymentMethodCode $paymentMethod, PaymentStatus $status, array $attributes = [])
    {
        return $this->paymentService->createOrderPayment($order, $paymentMethod, $status, $attributes);
    }
}
