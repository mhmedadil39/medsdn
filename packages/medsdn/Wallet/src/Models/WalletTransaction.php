<?php

namespace Webkul\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Webkul\Customer\Models\CustomerProxy;
use Webkul\Wallet\Contracts\WalletTransaction as WalletTransactionContract;
use Webkul\Wallet\Enums\WalletTransactionDirection;
use Webkul\Wallet\Enums\WalletTransactionStatus;
use Webkul\Wallet\Enums\WalletTransactionType;

class WalletTransaction extends Model implements WalletTransactionContract
{
    protected $table = 'wallet_transactions';

    protected $fillable = [
        'wallet_id',
        'customer_id',
        'type',
        'direction',
        'amount',
        'balance_before',
        'balance_after',
        'status',
        'reference_type',
        'reference_id',
        'source',
        'description',
        'meta',
        'created_by_type',
        'created_by_id',
        'entry_key',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'balance_before' => 'decimal:4',
        'balance_after' => 'decimal:4',
        'meta' => 'array',
        'type' => WalletTransactionType::class,
        'direction' => WalletTransactionDirection::class,
        'status' => WalletTransactionStatus::class,
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerProxy::modelClass(), 'customer_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'created_by_type', 'created_by_id');
    }
}
