<?php

namespace Webkul\MedsdnApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Auth;
use Webkul\MedsdnApi\Dto\CustomerPaymentOutput;
use Webkul\MedsdnApi\Exception\AuthenticationException;
use Webkul\MedsdnApi\Exception\ResourceNotFoundException;
use Webkul\Payment\Models\Payment;
use Webkul\Sales\Models\Order;
use Webkul\Wallet\Models\Wallet;

class CustomerPaymentProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $customer = $this->resolveCustomer();

        if (! $customer) {
            throw new AuthenticationException(trans('shop::app.customer.account.auth.unauthenticated'));
        }

        if (isset($uriVariables['id'])) {
            $payment = Payment::query()
                ->where('customer_id', $customer->id)
                ->with('payable')
                ->find($uriVariables['id']);

            if (! $payment) {
                throw new ResourceNotFoundException('Payment not found.');
            }

            return $this->map($payment);
        }

        $paginator = Payment::query()
            ->where('customer_id', $customer->id)
            ->with('payable')
            ->latest()
            ->paginate($context['filters']['per_page'] ?? 15);

        return [
            'data' => collect($paginator->items())->map(fn ($payment) => $this->map($payment))->all(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    protected function map($payment): CustomerPaymentOutput
    {
        $output = new CustomerPaymentOutput();
        $output->id = $payment->id;
        $output->paymentMethod = $payment->payment_method->value;
        $output->purpose = $payment->purpose->value;
        $output->amount = (float) $payment->amount;
        $output->currency = $payment->currency;
        $output->status = $payment->status->value;
        $output->externalReference = $payment->external_reference;
        $output->notes = $payment->notes;
        $output->createdAt = $payment->created_at?->toIso8601String();
        $output->paidAt = $payment->paid_at?->toIso8601String();
        $output->payable = $this->mapPayable($payment->payable);

        return $output;
    }

    protected function mapPayable($payable): ?array
    {
        return match (true) {
            $payable instanceof Order => [
                'type' => 'order',
                'id' => $payable->id,
                'increment_id' => $payable->increment_id,
                'status' => $payable->status,
            ],
            $payable instanceof Wallet => [
                'type' => 'wallet',
                'id' => $payable->id,
                'currency' => $payable->currency,
            ],
            default => null,
        };
    }

    protected function resolveCustomer(): ?object
    {
        return Auth::guard('sanctum')->user()
            ?: Auth::guard('api')->user()
            ?: Auth::guard('customer')->user();
    }
}
