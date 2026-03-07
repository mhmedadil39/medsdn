<?php

namespace Webkul\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Webkul\Customer\Models\CustomerProxy;
use Webkul\Wallet\Contracts\WalletHold as WalletHoldContract;
use Webkul\Wallet\Enums\WalletHoldStatus;

class WalletHold extends Model implements WalletHoldContract
{
    protected $table = 'wallet_holds';

    protected $fillable = [
        'wallet_id',
        'customer_id',
        'reference_type',
        'reference_id',
        'amount',
        'status',
        'expires_at',
        'released_at',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'meta' => 'array',
        'expires_at' => 'datetime',
        'released_at' => 'datetime',
        'status' => WalletHoldStatus::class,
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
}
