<?php

use Illuminate\Support\Facades\Route;
use Webkul\Payment\Http\Controllers\Admin\PaymentController;

Route::group([
    'middleware' => ['web', 'admin'],
    'prefix'     => config('app.admin_url').'/sales/payments',
], function () {
    Route::get('', [PaymentController::class, 'index'])
        ->name('admin.sales.payments.index');

    Route::get('view/{id}', [PaymentController::class, 'view'])
        ->name('admin.sales.payments.view');
});
