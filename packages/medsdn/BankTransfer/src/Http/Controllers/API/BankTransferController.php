<?php

namespace Webkul\BankTransfer\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Webkul\BankTransfer\Helpers\FileHelper;
use Webkul\BankTransfer\Http\Resources\BankTransferPaymentResource;
use Webkul\BankTransfer\Repositories\BankTransferRepository;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;
use Webkul\Shop\Http\Controllers\API\APIController;

class BankTransferController extends APIController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected BankTransferRepository $bankTransferRepository,
        protected OrderRepository $orderRepository
    ) {
    }

    /**
     * Get bank transfer payment method configuration.
     *
     * GET /api/bank-transfer/config
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConfig(): JsonResponse
    {
        try {
            $paymentMethod = payment()->getPaymentMethod('banktransfer');

            if (! $paymentMethod || ! $paymentMethod->isAvailable()) {
                return response()->json([
                    'success' => false,
                    'message' => trans('banktransfer::app.shop.errors.payment-method-not-available'),
                ], 404);
            }

            $bankAccounts = $paymentMethod->getBankAccounts();
            $instructions = core()->getConfigData('sales.payment_methods.banktransfer.instructions');

            return response()->json([
                'success' => true,
                'data' => [
                    'title' => $paymentMethod->getTitle(),
                    'description' => $paymentMethod->getDescription(),
                    'bank_accounts' => $bankAccounts,
                    'instructions' => $instructions,
                    'max_file_size' => '4MB',
                    'allowed_file_types' => ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Bank Transfer API - Get Config Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => trans('banktransfer::app.shop.errors.config-fetch-failed'),
            ], 500);
        }
    }

    /**
     * Upload payment proof and create order.
     *
     * POST /api/bank-transfer/upload
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
            'transaction_reference' => 'nullable|string|max:255',
        ], [
            'payment_proof.required' => trans('banktransfer::app.shop.errors.payment-proof-required'),
            'payment_proof.mimes' => trans('banktransfer::app.shop.errors.invalid-file-type'),
            'payment_proof.max' => trans('banktransfer::app.shop.errors.file-too-large', ['size' => '4MB']),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Validate cart
            if (Cart::hasError()) {
                return response()->json([
                    'success' => false,
                    'message' => trans('shop::app.checkout.cart.integrity.missing_fields'),
                ], 422);
            }

            // Validate payment method
            $cart = Cart::getCart();

            if (!$cart->payment || $cart->payment->method !== 'banktransfer') {
                return response()->json([
                    'success' => false,
                    'message' => trans('banktransfer::app.shop.errors.invalid-payment-method'),
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Collect cart totals
                Cart::collectTotals();

                // Validate order
                $this->validateOrder();

                // Create order
                $data = (new OrderResource($cart))->jsonSerialize();
                $order = $this->orderRepository->create($data);

                // Upload payment proof
                $file = $request->file('payment_proof');
                $filePath = FileHelper::store($file, $order->id);

                if (! $filePath) {
                    throw new \Exception(trans('banktransfer::app.shop.errors.upload-failed'));
                }

                // Create bank transfer payment record
                $paymentData = [
                    'order_id' => $order->id,
                    'customer_id' => $cart->customer_id,
                    'method_code' => 'banktransfer',
                    'transaction_reference' => $request->input('transaction_reference'),
                    'slip_path' => $filePath,
                    'status' => 'pending',
                ];

                $bankTransferPayment = $this->bankTransferRepository->create($paymentData);

                // Log successful upload
                Log::info('Bank Transfer API - Payment proof uploaded', [
                    'order_id' => $order->id,
                    'payment_id' => $bankTransferPayment->id,
                    'customer_id' => $cart->customer_id,
                ]);

                // Deactivate cart
                Cart::deActivateCart();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => trans('banktransfer::app.shop.messages.order-created-successfully'),
                    'data' => [
                        'order' => [
                            'id' => $order->id,
                            'increment_id' => $order->increment_id,
                            'status' => $order->status,
                            'grand_total' => $order->grand_total,
                            'created_at' => $order->created_at->toIso8601String(),
                        ],
                        'payment' => new BankTransferPaymentResource($bankTransferPayment),
                    ],
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('Bank Transfer API - Order creation failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'cart_id' => $cart->id ?? null,
                ]);

                throw $e;
            }
        } catch (\Exception $e) {
            // Log the actual exception details for debugging
            \Log::error('Bank Transfer order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'cart_id' => $cart->id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => trans('banktransfer::app.shop.errors.order-creation-failed'),
            ], 500);
        }
    }

    /**
     * Get customer's bank transfer payments.
     *
     * GET /api/bank-transfer/payments
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPayments(Request $request): JsonResponse
    {
        try {
            $customer = auth()->guard('customer')->user();

            if (! $customer) {
                return response()->json([
                    'success' => false,
                    'message' => trans('shop::app.customer.account.auth.unauthenticated'),
                ], 401);
            }

            $payments = $this->bankTransferRepository
                ->with(['order'])
                ->where('customer_id', $customer->id)
                ->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => BankTransferPaymentResource::collection($payments),
                'meta' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Bank Transfer API - Get Payments Error', [
                'error' => $e->getMessage(),
                'customer_id' => auth()->guard('customer')->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => trans('banktransfer::app.shop.errors.fetch-payments-failed'),
            ], 500);
        }
    }

    /**
     * Get specific payment details.
     *
     * GET /api/bank-transfer/payments/{id}
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPayment(int $id): JsonResponse
    {
        try {
            $customer = auth()->guard('customer')->user();

            if (! $customer) {
                return response()->json([
                    'success' => false,
                    'message' => trans('shop::app.customer.account.auth.unauthenticated'),
                ], 401);
            }

            $payment = $this->bankTransferRepository
                ->with(['order', 'reviewer'])
                ->where('customer_id', $customer->id)
                ->find($id);

            if (! $payment) {
                return response()->json([
                    'success' => false,
                    'message' => trans('banktransfer::app.shop.errors.payment-not-found'),
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new BankTransferPaymentResource($payment),
            ]);
        } catch (\Exception $e) {
            Log::error('Bank Transfer API - Get Payment Error', [
                'error' => $e->getMessage(),
                'payment_id' => $id,
                'customer_id' => auth()->guard('customer')->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => trans('banktransfer::app.shop.errors.fetch-payment-failed'),
            ], 500);
        }
    }

    /**
     * Get payment statistics for customer.
     *
     * GET /api/bank-transfer/statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $customer = auth()->guard('customer')->user();

            if (! $customer) {
                return response()->json([
                    'success' => false,
                    'message' => trans('shop::app.customer.account.auth.unauthenticated'),
                ], 401);
            }

            $statistics = [
                'total' => $this->bankTransferRepository
                    ->where('customer_id', $customer->id)
                    ->count(),
                'pending' => $this->bankTransferRepository
                    ->where('customer_id', $customer->id)
                    ->where('status', 'pending')
                    ->count(),
                'approved' => $this->bankTransferRepository
                    ->where('customer_id', $customer->id)
                    ->where('status', 'approved')
                    ->count(),
                'rejected' => $this->bankTransferRepository
                    ->where('customer_id', $customer->id)
                    ->where('status', 'rejected')
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('Bank Transfer API - Get Statistics Error', [
                'error' => $e->getMessage(),
                'customer_id' => auth()->guard('customer')->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => trans('banktransfer::app.shop.errors.fetch-statistics-failed'),
            ], 500);
        }
    }

    /**
     * Validate order before creation.
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function validateOrder(): void
    {
        $cart = Cart::getCart();

        // Validate minimum order amount
        $minimumOrderAmount = core()->getConfigData('sales.order_settings.minimum_order.minimum_order_amount') ?? 0;

        // Check if customer is suspended using cart relationship (works for both session and API requests)
        if (
            $cart->customer
            && $cart->customer->is_suspended
        ) {
            throw new \Exception(trans('shop::app.checkout.cart.suspended-account-message'));
        }

        if (! $minimumOrderAmount) {
            return;
        }

        $baseGrandTotal = $cart->base_grand_total;

        if ($baseGrandTotal < $minimumOrderAmount) {
            throw new \Exception(trans('shop::app.checkout.cart.minimum-order-message', ['amount' => core()->currency($minimumOrderAmount)]));
        }
    }
}
