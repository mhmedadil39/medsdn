<?php

namespace Webkul\BankTransfer\Repositories;

use Illuminate\Container\Container;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\BankTransfer\Events\PaymentApproved;
use Webkul\BankTransfer\Events\PaymentProofUploaded;
use Webkul\BankTransfer\Events\PaymentRejected;
use Webkul\Payment\Actions\RejectManualPaymentAction;
use Webkul\BankTransfer\Contracts\BankTransferPayment;
use Webkul\Core\Eloquent\Repository;
use Webkul\Payment\Actions\ApproveManualPaymentAction;

class BankTransferRepository extends Repository
{
    public function __construct(
        protected ApproveManualPaymentAction $approveManualPaymentAction,
        protected RejectManualPaymentAction $rejectManualPaymentAction,
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string
    {
        return 'Webkul\BankTransfer\Contracts\BankTransferPayment';
    }

    /**
     * Create a new bank transfer payment record.
     *
     * @param  array  $data
     * @return BankTransferPayment
     *
     * @throws \Exception
     */
    public function create(array $data)
    {
        try {
            // Set default values
            $data['method_code'] = $data['method_code'] ?? 'banktransfer';
            $data['status'] = $data['status'] ?? 'pending';
            $data['receipt_disk'] = $data['receipt_disk'] ?? 'private';
            $data['receipt_name'] = $data['receipt_name'] ?? basename($data['slip_path'] ?? '');

            $payment = parent::create($data);

            Event::dispatch(new PaymentProofUploaded($payment));

            return $payment;
        } catch (\Exception $e) {
            throw new \Exception('Failed to create bank transfer payment: '.$e->getMessage());
        }
    }

    /**
     * Find a bank transfer payment by ID.
     *
     * @param  int  $id
     * @return BankTransferPayment|null
     */
    public function find($id, $columns = ['*'])
    {
        return parent::find($id, $columns);
    }

    /**
     * Find a bank transfer payment by order ID.
     *
     * @param  int  $orderId
     * @return BankTransferPayment|null
     */
    public function findByOrderId(int $orderId): ?BankTransferPayment
    {
        return $this->findOneByField('order_id', $orderId);
    }

    /**
     * Get all pending bank transfer payments.
     *
     * @return Collection
     */
    public function getPending(): Collection
    {
        return $this->model
            ->pending()
            ->with(['order', 'customer'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Approve a bank transfer payment.
     *
     * @param  int  $id
     * @param  int  $adminId
     * @param  string|null  $note
     * @return bool
     *
     * @throws \Exception
     */
    public function approve(int $id, int $adminId, ?string $note = null): bool
    {
        try {
            $payment = $this->findOrFail($id);

            // Validate payment is pending
            if (! $payment->isPending()) {
                throw new \Exception('Payment has already been reviewed and cannot be approved again.');
            }

            return DB::transaction(function () use ($payment, $adminId, $note) {
                if ($payment->payment) {
                    $this->approveManualPaymentAction->handle($payment->payment, $adminId, $note);
                }

                $updated = $payment->update([
                    'status' => 'approved',
                    'reviewed_by' => $adminId,
                    'reviewed_at' => now(),
                    'admin_note' => $note,
                ]);

                Event::dispatch(new PaymentApproved($payment->fresh()));

                return $updated;
            });
        } catch (\Exception $e) {
            throw new \Exception('Failed to approve payment: '.$e->getMessage());
        }
    }

    /**
     * Reject a bank transfer payment.
     *
     * @param  int  $id
     * @param  int  $adminId
     * @param  string  $note
     * @return bool
     *
     * @throws \Exception
     */
    public function reject(int $id, int $adminId, string $note): bool
    {
        try {
            // Validate note is provided
            if (empty(trim($note))) {
                throw new \Exception('Admin note is required for rejection.');
            }

            $payment = $this->findOrFail($id);

            // Validate payment is pending
            if (! $payment->isPending()) {
                throw new \Exception('Payment has already been reviewed and cannot be rejected again.');
            }

            return DB::transaction(function () use ($payment, $adminId, $note) {
                if ($payment->payment) {
                    $this->rejectManualPaymentAction->handle($payment->payment, $adminId, $note, $note);
                }

                $updated = $payment->update([
                    'status' => 'rejected',
                    'reviewed_by' => $adminId,
                    'reviewed_at' => now(),
                    'admin_note' => $note,
                ]);

                Event::dispatch(new PaymentRejected($payment->fresh()));

                return $updated;
            });
        } catch (\Exception $e) {
            throw new \Exception('Failed to reject payment: '.$e->getMessage());
        }
    }

    /**
     * Get paginated list of bank transfer payments with filters.
     *
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function getList(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['order', 'customer', 'reviewer']);

        // Apply status filter
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('order', function ($orderQuery) use ($search) {
                    $orderQuery->where('increment_id', 'like', "%{$search}%");
                })
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhere('transaction_reference', 'like', "%{$search}%");
            });
        }

        // Apply date range filter
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Apply sorting with validation
        $allowedSortColumns = [
            'id',
            'order_id',
            'customer_id',
            'status',
            'created_at',
            'updated_at',
            'reviewed_at',
        ];
        
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'created_at';
        
        $sortOrder = strtolower($filters['sort_order'] ?? 'desc');
        $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc';
        
        $query->orderBy($sortBy, $sortOrder);

        // Validate and sanitize per_page parameter
        $perPage = $filters['per_page'] ?? 15;
        $perPage = (int) $perPage;
        $perPage = max(1, min(100, $perPage)); // Enforce minimum 1, maximum 100

        return $query->paginate($perPage);
    }
}
