<?php

namespace Webkul\MedsdnApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Webkul\BankTransfer\Actions\StoreBankTransferReceiptAction;
use Webkul\BankTransfer\Repositories\BankTransferRepository;
use Webkul\MedsdnApi\Dto\WalletTopupInput;
use Webkul\MedsdnApi\Dto\WalletTopupOutput;
use Webkul\MedsdnApi\Exception\AuthenticationException;
use Webkul\Payment\Actions\CreateWalletTopupPaymentAction;
use Webkul\Payment\Enums\PaymentMethodCode;

class WalletTopupProcessor implements ProcessorInterface
{
    public function __construct(
        protected CreateWalletTopupPaymentAction $createWalletTopupPaymentAction,
        protected StoreBankTransferReceiptAction $storeBankTransferReceiptAction,
        protected BankTransferRepository $bankTransferRepository
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (! $data instanceof WalletTopupInput) {
            throw new \InvalidArgumentException('Invalid wallet topup input.');
        }

        $customer = $this->resolveCustomer();

        if (! $customer) {
            throw new AuthenticationException(trans('shop::app.customer.account.auth.unauthenticated'));
        }

        return DB::transaction(function () use ($data, $customer) {
            $payment = $this->createWalletTopupPaymentAction->handle(
                customer: $customer,
                amount: (float) $data->amount,
                paymentMethod: PaymentMethodCode::BANK_TRANSFER,
                attributes: [
                    'settlement_key' => 'wallet-topup:'.$customer->id.':'.Str::ulid(),
                    'external_reference' => $data->transactionReference,
                    'bank_name' => $data->bankName,
                    'notes' => $data->notes,
                    'meta' => [
                        'source' => 'medsdn_api.wallet.topup',
                    ],
                ]
            );

            $receipt = $this->storeBankTransferReceiptAction->handle($data->paymentProof, $payment->id);

            $this->bankTransferRepository->create([
                'payment_id' => $payment->id,
                'order_id' => null,
                'customer_id' => $customer->id,
                'method_code' => PaymentMethodCode::BANK_TRANSFER->value,
                'transaction_reference' => $data->transactionReference,
                'slip_path' => $receipt['slip_path'],
                'receipt_disk' => $receipt['receipt_disk'],
                'receipt_name' => $receipt['receipt_name'],
                'receipt_mime' => $receipt['receipt_mime'],
                'receipt_size' => $receipt['receipt_size'],
                'bank_account_key' => $data->bankName,
                'status' => 'pending',
            ]);

            $output = new WalletTopupOutput();
            $output->paymentId = $payment->id;
            $output->success = true;
            $output->message = 'Wallet topup request submitted for review.';
            $output->status = $payment->status->value;
            $output->amount = (float) $payment->amount;

            return $output;
        });
    }

    protected function resolveCustomer(): ?object
    {
        return Auth::guard('sanctum')->user()
            ?: Auth::guard('api')->user()
            ?: Auth::guard('customer')->user();
    }
}
