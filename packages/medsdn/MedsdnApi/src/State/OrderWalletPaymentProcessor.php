<?php

namespace Webkul\MedsdnApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Webkul\Customer\Models\Customer;
use Webkul\MedsdnApi\Dto\OrderWalletPaymentInput;
use Webkul\MedsdnApi\Dto\PaymentActionOutput;
use Webkul\MedsdnApi\Exception\AuthenticationException;
use Webkul\MedsdnApi\Exception\ResourceNotFoundException;
use Webkul\Payment\Actions\PayOrderWithWalletAction;
use Webkul\Sales\Models\Order;

class OrderWalletPaymentProcessor implements ProcessorInterface
{
    public function __construct(
        protected PayOrderWithWalletAction $payOrderWithWalletAction
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $customer = $this->resolveCustomer();

        if (! $customer) {
            throw new AuthenticationException(trans('shop::app.customer.account.auth.unauthenticated'));
        }

        $orderId = $uriVariables['id']
            ?? ($data instanceof OrderWalletPaymentInput ? $data->orderId : null);

        $order = Order::query()
            ->where('customer_id', $customer->id)
            ->where('customer_type', Customer::class)
            ->find($orderId);

        if (! $order) {
            throw new ResourceNotFoundException('Order not found.');
        }

        $payment = $this->payOrderWithWalletAction->handle(
            $order,
            $customer,
            [
                'notes' => $data instanceof OrderWalletPaymentInput ? $data->notes : null,
                'meta' => [
                    'source' => 'medsdnapi.order.wallet-payment',
                ],
            ]
        );

        $output = new PaymentActionOutput();
        $output->success = true;
        $output->message = 'Order paid successfully using wallet.';
        $output->data = [
            'payment_id' => $payment->id,
            'status' => $payment->status->value,
            'order_id' => $order->id,
            'settlement_key' => $payment->settlement_key,
        ];

        return $output;
    }

    protected function resolveCustomer(): ?Customer
    {
        $customer = Auth::guard('sanctum')->user()
            ?: Auth::guard('api')->user()
            ?: Auth::guard('customer')->user();

        return $customer instanceof Customer ? $customer : null;
    }
}
