<?php

namespace Webkul\Shop\Http\Controllers\Customer\Account;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Webkul\BankTransfer\Actions\StoreBankTransferReceiptAction;
use Webkul\BankTransfer\Repositories\BankTransferRepository;
use Webkul\Payment\Actions\CreateWalletTopupPaymentAction;
use Webkul\Payment\Enums\PaymentMethodCode;
use Webkul\Payment\Models\Payment;
use Webkul\Shop\Http\Controllers\Controller;
use Webkul\Shop\Http\Requests\Customer\Account\WalletTopupRequest;
use Webkul\Wallet\Models\WalletTransaction;
use Webkul\Wallet\Services\WalletService;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
        protected CreateWalletTopupPaymentAction $createWalletTopupPaymentAction,
        protected StoreBankTransferReceiptAction $storeBankTransferReceiptAction,
        protected BankTransferRepository $bankTransferRepository
    ) {}

    public function index()
    {
        $customer = auth()->guard('customer')->user();
        $wallet = $this->walletService->resolveCustomerWallet($customer->id);

        $transactions = WalletTransaction::query()
            ->where('wallet_id', $wallet->id)
            ->latest()
            ->paginate(10, ['*'], 'transactions_page');

        $payments = Payment::query()
            ->where('customer_id', $customer->id)
            ->whereIn('purpose', ['wallet_topup', 'order_payment'])
            ->latest()
            ->paginate(10, ['*'], 'payments_page');

        return view('shop::customers.account.wallet.index', compact('wallet', 'transactions', 'payments'));
    }

    public function topup()
    {
        $paymentMethod = payment()->getPaymentMethod(PaymentMethodCode::BANK_TRANSFER->value);
        $bankAccounts = $paymentMethod ? $paymentMethod->getBankAccounts() : [];
        $instructions = core()->getConfigData('sales.payment_methods.banktransfer.instructions');

        return view('shop::customers.account.wallet.topup', compact('bankAccounts', 'instructions'));
    }

    public function storeTopup(WalletTopupRequest $request): RedirectResponse
    {
        $customer = auth()->guard('customer')->user();

        DB::transaction(function () use ($request, $customer) {
            $payment = $this->createWalletTopupPaymentAction->handle(
                customer: $customer,
                amount: (float) $request->float('amount'),
                attributes: [
                    'settlement_key' => 'wallet-topup:'.$customer->id.':'.Str::ulid(),
                    'external_reference' => $request->string('transaction_reference')->toString(),
                    'bank_name' => $request->string('bank_name')->toString() ?: null,
                    'notes' => $request->string('notes')->toString() ?: null,
                    'meta' => [
                        'source' => 'shop.customer.wallet.topup',
                    ],
                ]
            );

            $receipt = $this->storeBankTransferReceiptAction->handle(
                $request->file('payment_proof'),
                $payment->id
            );

            $this->bankTransferRepository->create([
                'payment_id' => $payment->id,
                'order_id' => null,
                'customer_id' => $customer->id,
                'method_code' => PaymentMethodCode::BANK_TRANSFER->value,
                'transaction_reference' => $request->string('transaction_reference')->toString(),
                'slip_path' => $receipt['slip_path'],
                'receipt_disk' => $receipt['receipt_disk'],
                'receipt_name' => $receipt['receipt_name'],
                'receipt_mime' => $receipt['receipt_mime'],
                'receipt_size' => $receipt['receipt_size'],
                'bank_account_key' => $request->string('bank_name')->toString() ?: null,
                'status' => 'pending',
            ]);
        });

        session()->flash('success', 'Wallet topup request submitted for manual review.');

        return redirect()->route('shop.customers.account.wallet.index');
    }
}
