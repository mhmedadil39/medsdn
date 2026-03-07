<?php

use Illuminate\Support\Facades\Route;
use Webkul\Wallet\Http\Controllers\Admin\WalletTransactionController;

Route::group([
    'middleware' => ['web', 'admin'],
    'prefix'     => config('app.admin_url').'/sales/wallet-transactions',
], function () {
    Route::get('', [WalletTransactionController::class, 'index'])
        ->name('admin.sales.wallet_transactions.index');
});
