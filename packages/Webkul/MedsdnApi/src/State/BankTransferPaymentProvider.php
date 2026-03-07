<?php

namespace Webkul\MedsdnApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Webkul\BankTransfer\Repositories\BankTransferRepository;
use Webkul\MedsdnApi\Dto\BankTransferPaymentOutput;
use Webkul\MedsdnApi\Exception\AuthenticationException;
use Webkul\MedsdnApi\Exception\ResourceNotFoundException;

/**
 * Provides bank transfer payment data for customers.
 */
class BankTransferPaymentProvider implements ProviderInterface
{
    public function __construct(
        protected BankTransferRepository $bankTransferRepository
    ) {
    }

    /**
     * Provide payment data.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        try {
            $customer = Auth::guard('customer')->user();

            if (! $customer) {
                throw new AuthenticationException(
                    trans('shop::app.customer.account.auth.unauthenticated')
                );
            }

            // Single payment query
            if (isset($uriVariables['id'])) {
                return $this->getPayment($uriVariables['id'], $customer->id);
            }

            // Collection query
            return $this->getPayments($customer->id, $context);
        } catch (AuthenticationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Bank Transfer API - Get Payments Error', [
                'error' => $e->getMessage(),
                'customer_id' => Auth::guard('customer')->id(),
            ]);

            throw new ResourceNotFoundException(
                trans('banktransfer::app.shop.errors.fetch-payments-failed')
            );
        }
    }

    /**
     * Get single payment.
     */
    protected function getPayment(int $id, int $customerId): BankTransferPaymentOutput
    {
        $payment = $this->bankTransferRepository
            ->with(['order', 'reviewer'])
            ->where('customer_id', $customerId)
            ->find($id);

        if (! $payment) {
            throw new ResourceNotFoundException(
                trans('banktransfer::app.shop.errors.payment-not-found')
            );
        }

        return $this->mapToOutput($payment);
    }

    /**
     * Get payments collection with pagination metadata.
     */
    protected function getPayments(int $customerId, array $context): array
    {
        $perPage = $context['filters']['per_page'] ?? 15;

        $paginator = $this->bankTransferRepository
            ->with(['order'])
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $outputs = [];
        foreach ($paginator->items() as $payment) {
            $outputs[] = $this->mapToOutput($payment);
        }

        return [
            'data' => $outputs,
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }

    /**
     * Map payment model to output DTO.
     */
    protected function mapToOutput($payment): BankTransferPaymentOutput
    {
        $output = new BankTransferPaymentOutput();
        $output->id = $payment->id;
        $output->orderId = $payment->order_id;
        $output->customerId = $payment->customer_id;
        $output->methodCode = $payment->method_code;
        $output->transactionReference = $payment->transaction_reference;
        $output->status = $payment->status;
        $output->statusLabel = $this->getStatusLabel($payment->status);
        $output->reviewedBy = $payment->reviewed_by;
        $output->reviewedAt = $payment->reviewed_at?->toIso8601String();
        $output->adminNote = $payment->admin_note;
        $output->createdAt = $payment->created_at->toIso8601String();
        $output->updatedAt = $payment->updated_at->toIso8601String();
        $output->isPending = $payment->isPending();
        $output->isApproved = $payment->isApproved();
        $output->isRejected = $payment->isRejected();

        // Include order data if loaded
        if ($payment->relationLoaded('order') && $payment->order) {
            $output->order = [
                'id' => $payment->order->id,
                'increment_id' => $payment->order->increment_id,
                'status' => $payment->order->status,
                'grand_total' => $payment->order->grand_total,
                'grand_total_formatted' => core()->formatPrice(
                    $payment->order->grand_total,
                    $payment->order->order_currency_code
                ),
                'created_at' => $payment->order->created_at->toIso8601String(),
            ];
        }

        // Include reviewer data if loaded
        if ($payment->relationLoaded('reviewer') && $payment->reviewer) {
            $output->reviewer = [
                'id' => $payment->reviewer->id,
                'name' => $payment->reviewer->name,
            ];
        }

        return $output;
    }

    /**
     * Get status label.
     */
    protected function getStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => trans('banktransfer::app.admin.datagrid.pending'),
            'approved' => trans('banktransfer::app.admin.datagrid.approved'),
            'rejected' => trans('banktransfer::app.admin.datagrid.rejected'),
            default => $status,
        };
    }
}
