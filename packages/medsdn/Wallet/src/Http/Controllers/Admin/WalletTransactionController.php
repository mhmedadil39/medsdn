<?php

namespace Webkul\Wallet\Http\Controllers\Admin;

use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Wallet\Models\WalletTransaction;

class WalletTransactionController extends Controller
{
    public function index()
    {
        $transactions = WalletTransaction::query()
            ->with(['customer', 'wallet', 'reference'])
            ->when(request()->filled('customer_id'), fn ($query) => $query->where('customer_id', request()->integer('customer_id')))
            ->when(request()->filled('wallet_id'), fn ($query) => $query->where('wallet_id', request()->integer('wallet_id')))
            ->latest()
            ->paginate(20);

        return view('wallet::admin.transactions.index', compact('transactions'));
    }
}
