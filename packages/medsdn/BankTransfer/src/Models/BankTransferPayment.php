<?php

namespace Webkul\BankTransfer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\BankTransfer\Contracts\BankTransferPayment as BankTransferPaymentContract;
use Webkul\Customer\Models\CustomerProxy;
use Webkul\Payment\Models\Payment;
use Webkul\Sales\Models\OrderProxy;
use Webkul\User\Models\AdminProxy;

class BankTransferPayment extends Model implements BankTransferPaymentContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bank_transfer_payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payment_id',
        'order_id',
        'customer_id',
        'method_code',
        'transaction_reference',
        'slip_path',
        'receipt_disk',
        'receipt_name',
        'receipt_mime',
        'receipt_size',
        'bank_account_key',
        'status',
        'reviewed_by',
        'reviewed_at',
        'admin_note',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'reviewed_at' => 'datetime',
        'receipt_size' => 'integer',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    /**
     * Get the order associated with this payment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderProxy::modelClass());
    }

    /**
     * Get the customer who made this payment.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerProxy::modelClass());
    }

    /**
     * Get the admin who reviewed this payment.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(AdminProxy::modelClass(), 'reviewed_by');
    }

    /**
     * Scope a query to only include pending payments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved payments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected payments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Get the full URL for the payment slip.
     * Returns a secure download route since slips are stored on private disk.
     *
     * @return string
     */
    public function getSlipUrlAttribute(): string
    {
        if (empty($this->slip_path) && empty($this->receipt_name)) {
            return '';
        }

        // Return route to secure download endpoint instead of direct Storage::url()
        // since files are on private disk
        return route('admin.sales.bank-transfers.file', ['id' => $this->id]);
    }

    /**
     * Check if the payment is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the payment is approved.
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the payment is rejected.
     *
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
