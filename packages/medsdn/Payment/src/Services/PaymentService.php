<?php

namespace Webkul\Payment\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Webkul\Customer\Models\Customer;
use Webkul\Payment\Enums\PaymentMethodCode;
use Webkul\Payment\Enums\PaymentPurpose;
use Webkul\Payment\Enums\PaymentStatus;
use Webkul\Payment\Models\Payment;
use Webkul\Sales\Models\Invoice;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Wallet\Actions\CreditWalletAction;
use Webkul\Wallet\Actions\DebitWalletAction;
use Webkul\Wallet\Services\WalletService;

class PaymentService
{
    public function __construct(
        protected WalletService $walletService,
        protected CreditWalletAction $creditWalletAction,
        protected DebitWalletAction $debitWalletAction,
        protected InvoiceRepository $invoiceRepository
    ) {}

    public function create(array $data): Payment
    {
        return Payment::query()->firstOrCreate(
            ['settlement_key' => $data['settlement_key']],
            $data
        );
    }

    public function createWalletTopup(
        Customer $customer,
        float $amount,
        PaymentMethodCode $paymentMethod,
        array $attributes = []
    ): Payment {
        $wallet = $this->walletService->resolveCustomerWallet($customer->id);

        return $this->create([
            'customer_id' => $customer->id,
            'payable_type' => $wallet::class,
            'payable_id' => $wallet->id,
            'payment_method' => $paymentMethod,
            'purpose' => PaymentPurpose::WALLET_TOPUP,
            'amount' => $amount,
            'currency' => $wallet->currency,
            'status' => $attributes['status'] ?? PaymentStatus::PENDING_REVIEW,
            'settlement_key' => $attributes['settlement_key'] ?? 'wallet-topup:'.Str::ulid(),
            'external_reference' => $attributes['external_reference'] ?? null,
            'bank_name' => $attributes['bank_name'] ?? null,
            'notes' => $attributes['notes'] ?? null,
            'meta' => $attributes['meta'] ?? [],
        ]);
    }

    public function createOrderPayment(
        Order $order,
        PaymentMethodCode $paymentMethod,
        PaymentStatus $status,
        array $attributes = []
    ): Payment {
        $payment = $this->create([
            'customer_id' => $order->customer_id,
            'payable_type' => $order::class,
            'payable_id' => $order->id,
            'payment_method' => $paymentMethod,
            'purpose' => PaymentPurpose::ORDER_PAYMENT,
            'amount' => (float) $order->base_grand_total,
            'currency' => $order->base_currency_code,
            'status' => $status,
            'settlement_key' => $attributes['settlement_key'] ?? 'order:'.$order->id.':'.$paymentMethod->value,
            'external_reference' => $attributes['external_reference'] ?? null,
            'bank_name' => $attributes['bank_name'] ?? null,
            'notes' => $attributes['notes'] ?? null,
            'meta' => $attributes['meta'] ?? [],
        ]);

        $this->syncOrderPaymentSnapshot($order, $payment);

        return $payment;
    }

    public function approveManual(Payment $payment, int $adminId, ?string $adminNotes = null): Payment
    {
        return DB::transaction(function () use ($payment, $adminId, $adminNotes) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($payment->isFulfilled()) {
                return $payment;
            }

            if (! $payment->isPendingReview()) {
                throw new RuntimeException('Only pending review payments can be approved.');
            }

            $payment->forceFill([
                'status' => PaymentStatus::APPROVED,
                'reviewed_by' => $adminId,
                'reviewed_at' => now(),
                'approved_at' => now(),
                'admin_notes' => $adminNotes,
            ])->save();

            $payment = match ($payment->purpose) {
                PaymentPurpose::WALLET_TOPUP => $this->settleWalletTopup($payment),
                PaymentPurpose::ORDER_PAYMENT => $this->settleOrderPayment($payment),
            };

            return $payment->fresh();
        });
    }

    public function rejectManual(Payment $payment, int $adminId, string $reason, ?string $adminNotes = null): Payment
    {
        return DB::transaction(function () use ($payment, $adminId, $reason, $adminNotes) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($payment->isFulfilled() || $payment->status !== PaymentStatus::PENDING_REVIEW) {
                throw new RuntimeException('Only pending review payments can be rejected.');
            }

            $payment->forceFill([
                'status' => PaymentStatus::REJECTED,
                'reviewed_by' => $adminId,
                'reviewed_at' => now(),
                'rejected_at' => now(),
                'rejection_reason' => $reason,
                'admin_notes' => $adminNotes,
            ])->save();

            if ($payment->payable instanceof Order) {
                $this->syncOrderPaymentSnapshot($payment->payable, $payment);
            }

            return $payment;
        });
    }

    public function payOrderWithWallet(Order $order, Customer $customer, array $attributes = []): Payment
    {
        return DB::transaction(function () use ($order, $customer, $attributes) {
            $wallet = $this->walletService->resolveCustomerWallet($customer->id, $order->base_currency_code);

            if ($wallet->currency !== $order->base_currency_code) {
                throw new RuntimeException('Wallet currency does not match order currency.');
            }

            $payment = $this->createOrderPayment(
                order: $order,
                paymentMethod: PaymentMethodCode::WALLET,
                status: PaymentStatus::PAID,
                attributes: [
                    'settlement_key' => $attributes['settlement_key'] ?? 'order:'.$order->id.':wallet',
                    'notes' => $attributes['notes'] ?? null,
                    'meta' => array_merge($attributes['meta'] ?? [], ['channel' => 'wallet']),
                ]
            );

            if (! $payment->isFulfilled()) {
                $this->debitWalletAction->handle($wallet, (float) $payment->amount, 'payment:'.$payment->id.':wallet-debit', [
                    'reference_type' => $order::class,
                    'reference_id' => $order->id,
                    'description' => 'Order payment via wallet',
                    'source' => 'payment',
                ]);

                $this->markPaymentPaid($payment);
                $this->createInvoiceForOrder($order);
            }

            return $payment->fresh();
        });
    }

    public function settleWalletTopup(Payment $payment): Payment
    {
        $wallet = $payment->payable ?: $this->walletService->resolveCustomerWallet($payment->customer_id, $payment->currency);

        $this->creditWalletAction->handle($wallet, (float) $payment->amount, 'payment:'.$payment->id.':wallet-credit', [
            'reference_type' => $payment::class,
            'reference_id' => $payment->id,
            'description' => 'Wallet topup approved manually',
            'source' => 'payment',
        ]);

        return $this->markPaymentPaid($payment);
    }

    public function settleOrderPayment(Payment $payment): Payment
    {
        $order = $payment->payable;

        if (! $order instanceof Order) {
            throw new RuntimeException('Order payment must be linked to an order.');
        }

        $this->createInvoiceForOrder($order);

        return $this->markPaymentPaid($payment);
    }

    protected function markPaymentPaid(Payment $payment): Payment
    {
        $payment->forceFill([
            'status' => PaymentStatus::PAID,
            'paid_at' => now(),
            'fulfilled_at' => now(),
        ])->save();

        if ($payment->payable instanceof Order) {
            $this->syncOrderPaymentSnapshot($payment->payable, $payment);
        }

        return $payment;
    }

    protected function createInvoiceForOrder(Order $order): void
    {
        if (! $order->canInvoice()) {
            return;
        }

        $invoiceData = ['order_id' => $order->id];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        $this->invoiceRepository->create($invoiceData, Invoice::STATUS_PAID);
    }

    protected function syncOrderPaymentSnapshot(Order $order, Payment $payment): void
    {
        if (! $order->payment) {
            return;
        }

        $additional = $order->payment->additional ?? [];
        $additional['payment_id'] = $payment->id;
        $additional['payment_status'] = $payment->status->value;
        $additional['settlement_key'] = $payment->settlement_key;
        $additional['purpose'] = $payment->purpose->value;

        if ($payment->external_reference) {
            $additional['external_reference'] = $payment->external_reference;
        }

        $order->payment->forceFill([
            'additional' => $additional,
        ])->save();
    }
}
