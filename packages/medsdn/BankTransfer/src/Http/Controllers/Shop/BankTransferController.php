<?php

namespace Webkul\BankTransfer\Http\Controllers\Shop;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\BankTransfer\Actions\StoreBankTransferReceiptAction;
use Webkul\BankTransfer\Http\Requests\PaymentProofRequest;
use Webkul\BankTransfer\Repositories\BankTransferRepository;
use Webkul\Checkout\Facades\Cart;
use Webkul\Payment\Actions\CreateOrderPaymentAction;
use Webkul\Payment\Enums\PaymentMethodCode;
use Webkul\Payment\Enums\PaymentStatus;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;
use Webkul\Shop\Http\Controllers\Controller;

class BankTransferController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected BankTransferRepository $bankTransferRepository,
        protected OrderRepository $orderRepository,
        protected CreateOrderPaymentAction $createOrderPaymentAction,
        protected StoreBankTransferReceiptAction $storeBankTransferReceiptAction
    ) {
    }

    /**
     * Handle payment proof upload and order creation.
     *
     * @param  \Webkul\BankTransfer\Http\Requests\PaymentProofRequest  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function upload(PaymentProofRequest $request): JsonResponse|RedirectResponse
    {
        try {
            // Validate cart
            if (Cart::hasError()) {
                return $this->errorResponse(
                    trans('shop::app.checkout.cart.integrity.missing_fields'),
                    route('shop.checkout.cart.index')
                );
            }

            // Validate payment method is bank transfer
            $cart = Cart::getCart();

            if (!$cart->payment || $cart->payment->method !== 'banktransfer') {
                return $this->errorResponse(
                    trans('banktransfer::app.shop.errors.invalid-payment-method'),
                    route('shop.checkout.onepage.index')
                );
            }

            DB::beginTransaction();

            try {
                // Collect cart totals
                Cart::collectTotals();

                // Validate order can be created
                $this->validateOrder();

                // Create order
                $data = (new OrderResource($cart))->jsonSerialize();
                $order = $this->orderRepository->create($data);
                $order->forceFill(['status' => Order::STATUS_PENDING_PAYMENT])->save();

                // Upload payment proof
                $file = $request->file('payment_proof');
                $receipt = $this->storeBankTransferReceiptAction->handle($file, $order->id);
                $payment = $this->createOrderPaymentAction->handle(
                    order: $order,
                    paymentMethod: PaymentMethodCode::BANK_TRANSFER,
                    status: PaymentStatus::PENDING_REVIEW,
                    attributes: [
                        'external_reference' => $request->input('transaction_reference'),
                        'notes' => 'Order awaiting manual bank transfer review',
                        'meta' => [
                            'source' => 'shop.checkout.banktransfer',
                        ],
                    ]
                );

                // Create bank transfer payment record
                $paymentData = [
                    'payment_id' => $payment->id,
                    'order_id' => $order->id,
                    'customer_id' => $cart->customer_id,
                    'method_code' => 'banktransfer',
                    'transaction_reference' => $request->input('transaction_reference'),
                    'slip_path' => $receipt['slip_path'],
                    'receipt_disk' => $receipt['receipt_disk'],
                    'receipt_name' => $receipt['receipt_name'],
                    'receipt_mime' => $receipt['receipt_mime'],
                    'receipt_size' => $receipt['receipt_size'],
                    'status' => 'pending',
                ];

                $bankTransferPayment = $this->bankTransferRepository->create($paymentData);

                // Log successful upload
                Log::info('Bank transfer payment proof uploaded', [
                    'order_id' => $order->id,
                    'payment_id' => $bankTransferPayment->id,
                    'generic_payment_id' => $payment->id,
                    'customer_id' => $cart->customer_id,
                ]);

                // Deactivate cart
                Cart::deActivateCart();

                // Store order ID in session for success page
                session()->flash('order_id', $order->id);

                DB::commit();

                // Return success response
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'redirect_url' => route('shop.checkout.onepage.success'),
                        'order_id' => $order->id,
                    ]);
                }

                return redirect()->route('shop.checkout.onepage.success');
            } catch (\Exception $e) {
                DB::rollBack();

                // Log error
                Log::error('Bank transfer order creation failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'cart_id' => $cart->id ?? null,
                ]);

                throw $e;
            }
        } catch (\Exception $e) {
            return $this->errorResponse(
                $e->getMessage() ?: trans('banktransfer::app.shop.errors.order-creation-failed'),
                route('shop.checkout.onepage.index')
            );
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

        if (
            auth()->guard('customer')->check()
            && auth()->guard('customer')->user()->is_suspended
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

    /**
     * Return error response.
     *
     * @param  string  $message
     * @param  string  $redirectUrl
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function errorResponse(string $message, string $redirectUrl): JsonResponse|RedirectResponse
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'redirect_url' => $redirectUrl,
            ], 422);
        }

        session()->flash('error', $message);

        return redirect($redirectUrl);
    }
}
