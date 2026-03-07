<?php

namespace Webkul\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Customer\Models\CustomerProxy;
use Webkul\Wallet\Contracts\Wallet as WalletContract;
use Webkul\Wallet\Enums\WalletStatus;

class Wallet extends Model implements WalletContract
{
    protected $table = 'wallets';

    protected $fillable = [
        'customer_id',
        'currency',
        'balance',
        'available_balance',
        'held_balance',
        'status',
    ];

    protected $casts = [
        'balance' => 'decimal:4',
        'available_balance' => 'decimal:4',
        'held_balance' => 'decimal:4',
        'status' => WalletStatus::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerProxy::modelClass(), 'customer_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id');
    }

    public function holds(): HasMany
    {
        return $this->hasMany(WalletHold::class, 'wallet_id');
    }

    public function isActive(): bool
    {
        return $this->status === WalletStatus::ACTIVE;
    }
}
