<?php

use Illuminate\Support\Facades\Route;
use Webkul\BankTransfer\Http\Controllers\Admin\BankTransferController;

Route::group([
    'middleware' => ['web', 'admin'],
    'prefix' => config('app.admin_url').'/sales/bank-transfers',
], function () {
    Route::get('', [BankTransferController::class, 'index'])
        ->name('admin.sales.bank-transfers.index');

    Route::get('view/{id}', [BankTransferController::class, 'view'])
        ->name('admin.sales.bank-transfers.view');

    Route::get('file/{id}', [BankTransferController::class, 'downloadFile'])
        ->name('admin.sales.bank-transfers.file');

    Route::post('{id}/approve', [BankTransferController::class, 'approve'])
        ->name('admin.sales.bank-transfers.approve');

    Route::post('{id}/reject', [BankTransferController::class, 'reject'])
        ->name('admin.sales.bank-transfers.reject');
});
