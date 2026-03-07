<?php

use Illuminate\Support\Facades\Route;
use Webkul\BankTransfer\Http\Controllers\API\BankTransferController;

/**
 * Bank Transfer API routes.
 *
 * These routes are designed for mobile applications and headless implementations.
 * All routes are prefixed with /api/bank-transfer
 */
Route::group(['prefix' => 'api/bank-transfer'], function () {
    /**
     * Public routes - No authentication required
     */
    
    /**
     * Get bank transfer configuration.
     *
     * Returns bank account details, instructions, and file upload requirements.
     *
     * @method GET
     * @route /api/bank-transfer/config
     * @response {
     *   "success": true,
     *   "data": {
     *     "title": "Bank Transfer",
     *     "description": "Pay via bank transfer",
     *     "bank_accounts": [...],
     *     "instructions": "...",
     *     "max_file_size": "4MB",
     *     "allowed_file_types": ["jpg", "jpeg", "png", "webp", "pdf"]
     *   }
     * }
     */
    Route::get('config', [BankTransferController::class, 'getConfig'])
        ->name('shop.api.bank-transfer.config');

    /**
     * Upload payment proof and create order.
     *
     * Requires multipart/form-data with payment_proof file.
     * Rate limited to 5 uploads per minute per user.
     *
     * @method POST
     * @route /api/bank-transfer/upload
     * @middleware throttle:5,1
     * @body {
     *   "payment_proof": File (required, jpg|jpeg|png|webp|pdf, max:4MB),
     *   "transaction_reference": String (optional, max:255)
     * }
     * @response {
     *   "success": true,
     *   "message": "Order created successfully",
     *   "data": {
     *     "order": {...},
     *     "payment": {...}
     *   }
     * }
     */
    Route::post('upload', [BankTransferController::class, 'upload'])
        ->middleware(['throttle:5,1'])
        ->name('shop.api.bank-transfer.upload');

    /**
     * Protected routes - Require customer authentication
     */
    Route::group(['middleware' => ['auth:sanctum', 'customer']], function () {
        /**
         * Get customer's bank transfer payments.
         *
         * Returns paginated list of customer's payments.
         *
         * @method GET
         * @route /api/bank-transfer/payments
         * @query {
         *   "per_page": Integer (optional, default: 15)
         * }
         * @response {
         *   "success": true,
         *   "data": [...],
         *   "meta": {
         *     "current_page": 1,
         *     "last_page": 5,
         *     "per_page": 15,
         *     "total": 75
         *   }
         * }
         */
        Route::get('payments', [BankTransferController::class, 'getPayments'])
            ->name('shop.api.bank-transfer.payments.index');

        /**
         * Get specific payment details.
         *
         * Returns detailed information about a specific payment.
         *
         * @method GET
         * @route /api/bank-transfer/payments/{id}
         * @param {id} Integer - Payment ID
         * @response {
         *   "success": true,
         *   "data": {
         *     "id": 1,
         *     "order": {...},
         *     "status": "pending",
         *     "transaction_reference": "TXN123",
         *     ...
         *   }
         * }
         */
        Route::get('payments/{id}', [BankTransferController::class, 'getPayment'])
            ->name('shop.api.bank-transfer.payments.show');

        /**
         * Get payment statistics.
         *
         * Returns count of payments by status for the authenticated customer.
         *
         * @method GET
         * @route /api/bank-transfer/statistics
         * @response {
         *   "success": true,
         *   "data": {
         *     "total": 10,
         *     "pending": 3,
         *     "approved": 6,
         *     "rejected": 1
         *   }
         * }
         */
        Route::get('statistics', [BankTransferController::class, 'getStatistics'])
            ->name('shop.api.bank-transfer.statistics');
    });
});
