<?php

namespace Webkul\MedsdnApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Webkul\BankTransfer\Actions\StoreBankTransferReceiptAction;
use Webkul\BankTransfer\Repositories\BankTransferRepository;
use Webkul\Checkout\Facades\Cart;
use Webkul\MedsdnApi\Dto\BankTransferPaymentInput;
use Webkul\MedsdnApi\Dto\BankTransferPaymentOutput;
use Webkul\MedsdnApi\Exception\InvalidInputException;
use Webkul\MedsdnApi\Exception\OperationFailedException;
use Webkul\MedsdnApi\Facades\CartTokenFacade;
use Webkul\Payment\Actions\CreateOrderPaymentAction;
use Webkul\Payment\Enums\PaymentMethodCode;
use Webkul\Payment\Enums\PaymentStatus;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;

/**
 * Processes bank transfer payment upload and order creation.
 */
class BankTransferPaymentProcessor implements ProcessorInterface
{
    public function __construct(
        protected BankTransferRepository $bankTransferRepository,
        protected OrderRepository $orderRepository,
        protected CreateOrderPaymentAction $createOrderPaymentAction,
        protected StoreBankTransferReceiptAction $storeBankTransferReceiptAction
    ) {
    }

    /**
     * Process payment upload.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (! $data instanceof BankTransferPaymentInput) {
            throw new InvalidInputException('Invalid input data');
        }

        // Validate input
        $this->validateInput($data);

        try {
            // Get cart from token
            $cart = $this->getCart($data->cartToken);
            Cart::setCart($cart);

            // Validate cart
            if (Cart::hasError()) {
                throw new InvalidInputException(
                    trans('shop::app.checkout.cart.integrity.missing_fields')
                );
            }

            // Validate payment method
            if (! $cart->payment || $cart->payment->method !== 'banktransfer') {
                throw new InvalidInputException(
                    trans('banktransfer::app.shop.errors.invalid-payment-method')
                );
            }

            DB::beginTransaction();

            try {
                // Collect cart totals
                Cart::collectTotals();

                // Validate order
                $this->validateOrder($cart);

                // Create order
                $orderData = (new OrderResource($cart))->jsonSerialize();
                $order = $this->orderRepository->create($orderData);
                $order->forceFill(['status' => Order::STATUS_PENDING_PAYMENT])->save();

                // Upload payment proof
                $receipt = $this->storeBankTransferReceiptAction->handle($data->paymentProof, $order->id);

                $payment = $this->createOrderPaymentAction->handle(
                    order: $order,
                    paymentMethod: PaymentMethodCode::BANK_TRANSFER,
                    status: PaymentStatus::PENDING_REVIEW,
                    attributes: [
                        'external_reference' => $data->transactionReference,
                        'notes' => 'Order awaiting manual bank transfer review',
                        'meta' => [
                            'source' => 'medsdnapi.banktransfer.upload',
                        ],
                    ]
                );

                // Create bank transfer payment record
                $paymentData = [
                    'payment_id' => $payment->id,
                    'order_id' => $order->id,
                    'customer_id' => $cart->customer_id,
                    'method_code' => 'banktransfer',
                    'transaction_reference' => $data->transactionReference,
                    'slip_path' => $receipt['slip_path'],
                    'receipt_disk' => $receipt['receipt_disk'],
                    'receipt_name' => $receipt['receipt_name'],
                    'receipt_mime' => $receipt['receipt_mime'],
                    'receipt_size' => $receipt['receipt_size'],
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

                // Prepare output
                $output = new BankTransferPaymentOutput();
                $output->success = true;
                $output->message = trans('banktransfer::app.shop.messages.order-created-successfully');
                $output->data = [
                    'order' => [
                        'id' => $order->id,
                        'increment_id' => $order->increment_id,
                        'status' => $order->status,
                        'grand_total' => $order->grand_total,
                        'created_at' => $order->created_at->toIso8601String(),
                    ],
                    'payment' => [
                        'id' => $bankTransferPayment->id,
                        'payment_id' => $payment->id,
                        'order_id' => $bankTransferPayment->order_id,
                        'method_code' => $bankTransferPayment->method_code,
                        'transaction_reference' => $bankTransferPayment->transaction_reference,
                        'status' => $bankTransferPayment->status,
                        'payment_status' => $payment->status->value,
                        'status_label' => trans('banktransfer::app.admin.datagrid.pending'),
                        'created_at' => $bankTransferPayment->created_at->toIso8601String(),
                        'is_pending' => true,
                        'is_approved' => false,
                        'is_rejected' => false,
                    ],
                ];

                return $output;
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('Bank Transfer API - Order creation failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'cart_id' => $cart->id ?? null,
                ]);

                throw $e;
            }
        } catch (InvalidInputException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new OperationFailedException(
                $e->getMessage() ?: trans('banktransfer::app.shop.errors.order-creation-failed')
            );
        }
    }

    /**
     * Validate input data.
     */
    protected function validateInput(BankTransferPaymentInput $data): void
    {
        $validator = Validator::make([
            'payment_proof' => $data->paymentProof,
            'transaction_reference' => $data->transactionReference,
        ], [
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
            'transaction_reference' => 'nullable|string|max:255',
        ], [
            'payment_proof.required' => trans('banktransfer::app.shop.errors.payment-proof-required'),
            'payment_proof.mimes' => trans('banktransfer::app.shop.errors.invalid-file-type'),
            'payment_proof.max' => trans('banktransfer::app.shop.errors.file-too-large', ['size' => '4MB']),
        ]);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first());
        }
    }

    /**
     * Get cart from token.
     */
    protected function getCart(?string $token)
    {
        if (! $token) {
            throw new InvalidInputException(
                trans('medsdnapi::app.graphql.cart.authentication-required')
            );
        }

        $cart = CartTokenFacade::getCartByToken($token);

        if (! $cart) {
            throw new InvalidInputException(
                trans('medsdnapi::app.graphql.cart.invalid-token')
            );
        }

        return $cart;
    }

    /**
     * Validate order before creation.
     */
    protected function validateOrder($cart): void
    {
        // Validate minimum order amount
        $minimumOrderAmount = core()->getConfigData('sales.order_settings.minimum_order.minimum_order_amount') ?? 0;

        // Check if customer is suspended using cart relationship (works for both session and API requests)
        if (
            $cart->customer
            && $cart->customer->is_suspended
        ) {
            throw new InvalidInputException(
                trans('shop::app.checkout.cart.suspended-account-message')
            );
        }

        if (! $minimumOrderAmount) {
            return;
        }

        $baseGrandTotal = $cart->base_grand_total;

        if ($baseGrandTotal < $minimumOrderAmount) {
            throw new InvalidInputException(
                trans('shop::app.checkout.cart.minimum-order-message', [
                    'amount' => core()->currency($minimumOrderAmount),
                ])
            );
        }
    }
}
