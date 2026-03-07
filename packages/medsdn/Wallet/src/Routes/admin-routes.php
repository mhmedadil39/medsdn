<?php

use Illuminate\Support\Facades\Route;
use Webkul\Wallet\Http\Controllers\Admin\WalletController;

Route::group([
    'middleware' => ['web', 'admin'],
    'prefix'     => config('app.admin_url').'/customers/wallets',
], function () {
    Route::get('', [WalletController::class, 'index'])
        ->name('admin.customers.wallets.index');

    Route::post('manage', [WalletController::class, 'manage'])
        ->name('admin.customers.wallets.manage');

    Route::get('view/{id}', [WalletController::class, 'view'])
        ->name('admin.customers.wallets.view');

    Route::post('adjust/{id}', [WalletController::class, 'adjust'])
        ->name('admin.customers.wallets.adjust');
});
