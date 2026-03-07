<?php

namespace Webkul\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Webkul\Customer\Models\CustomerProxy;
use Webkul\Payment\Contracts\Payment as PaymentContract;
use Webkul\Payment\Enums\PaymentMethodCode;
use Webkul\Payment\Enums\PaymentPurpose;
use Webkul\Payment\Enums\PaymentStatus;
use Webkul\User\Models\AdminProxy;

class Payment extends Model implements PaymentContract
{
    protected $table = 'payments';

    protected $fillable = [
        'customer_id',
        'payable_type',
        'payable_id',
        'payment_method',
        'purpose',
        'amount',
        'currency',
        'status',
        'settlement_key',
        'external_reference',
        'bank_name',
        'notes',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'approved_at',
        'rejected_at',
        'paid_at',
        'fulfilled_at',
        'rejection_reason',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'meta' => 'array',
        'status' => PaymentStatus::class,
        'purpose' => PaymentPurpose::class,
        'payment_method' => PaymentMethodCode::class,
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'paid_at' => 'datetime',
        'fulfilled_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerProxy::modelClass(), 'customer_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(AdminProxy::modelClass(), 'reviewed_by');
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPendingReview(): bool
    {
        return $this->status === PaymentStatus::PENDING_REVIEW;
    }

    public function isFulfilled(): bool
    {
        return $this->fulfilled_at !== null;
    }
}
