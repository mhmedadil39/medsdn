<?php

namespace Webkul\Wallet\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use RuntimeException;
use Illuminate\Support\Str;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Customer\Models\Customer;
use Webkul\Wallet\Http\Requests\Admin\WalletManagementRequest;
use Webkul\Wallet\Enums\WalletTransactionType;
use Webkul\Wallet\Models\Wallet;
use Webkul\Wallet\Models\WalletTransaction;
use Webkul\Wallet\Services\WalletService;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    public function index()
    {
        $wallets = Wallet::query()
            ->with('customer')
            ->latest()
            ->paginate(20);

        $customers = Customer::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        $selectedCustomerId = request()->integer('customer_id') ?: null;
        $selectedAction = request()->get('action', 'credit');

        return view('wallet::admin.index', compact('wallets', 'customers', 'selectedCustomerId', 'selectedAction'));
    }

    public function view(int $id)
    {
        $wallet = Wallet::query()
            ->with('customer')
            ->findOrFail($id);

        $transactions = WalletTransaction::query()
            ->where('wallet_id', $wallet->id)
            ->latest()
            ->paginate(20);

        return view('wallet::admin.view', compact('wallet', 'transactions'));
    }

    public function adjust(int $id): RedirectResponse
    {
        $data = request()->validate([
            'amount' => ['required', 'numeric', 'not_in:0'],
            'description' => ['required', 'string', 'max:500'],
        ]);

        $wallet = Wallet::query()->findOrFail($id);

        $this->walletService->adjust(
            wallet: $wallet,
            amount: (float) $data['amount'],
            entryKey: 'admin-adjustment:'.$wallet->id.':'.Str::ulid(),
            context: [
                'description' => $data['description'],
                'source' => 'admin.wallet.adjustment',
                'created_by_type' => 'admin',
                'created_by_id' => auth()->guard('admin')->id(),
            ]
        );

        session()->flash('success', 'Wallet adjusted successfully.');

        return redirect()->route('admin.customers.wallets.view', $wallet->id);
    }

    public function manage(WalletManagementRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $wallet = $this->walletService->resolveCustomerWallet((int) $data['customer_id']);

        try {
            if ($data['action_type'] === 'credit') {
                $this->walletService->credit(
                    wallet: $wallet,
                    amount: (float) $data['amount'],
                    type: WalletTransactionType::ADJUSTMENT,
                    entryKey: 'admin-wallet-credit:'.$wallet->id.':'.Str::ulid(),
                    context: [
                        'description' => $data['description'],
                        'source' => 'admin.wallet.credit',
                        'created_by_type' => 'admin',
                        'created_by_id' => auth()->guard('admin')->id(),
                    ]
                );

                session()->flash('success', 'Wallet credited successfully.');
            } else {
                $this->walletService->debit(
                    wallet: $wallet,
                    amount: (float) $data['amount'],
                    type: WalletTransactionType::ADJUSTMENT,
                    entryKey: 'admin-wallet-debit:'.$wallet->id.':'.Str::ulid(),
                    context: [
                        'description' => $data['description'],
                        'source' => 'admin.wallet.debit',
                        'created_by_type' => 'admin',
                        'created_by_id' => auth()->guard('admin')->id(),
                    ]
                );

                session()->flash('success', 'Wallet debited successfully.');
            }
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('admin.customers.wallets.index', [
                    'customer_id' => $wallet->customer_id,
                    'action'      => $data['action_type'],
                ])
                ->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.customers.wallets.view', $wallet->id);
    }
}
