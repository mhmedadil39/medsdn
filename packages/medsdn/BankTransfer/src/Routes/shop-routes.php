<?php

use Illuminate\Support\Facades\Route;
use Webkul\BankTransfer\Http\Controllers\Shop\BankTransferController;

/**
 * Bank Transfer shop routes.
 */
Route::group([
    'middleware' => ['web', 'locale', 'currency', 'theme'],
    'prefix' => 'checkout/bank-transfer',
], function () {
    /**
     * Upload payment proof and create order.
     */
    Route::post('upload', [BankTransferController::class, 'upload'])
        ->middleware(['customer', 'throttle:5,1'])
        ->name('shop.checkout.bank-transfer.upload');
});
