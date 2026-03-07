<?php

use Illuminate\Support\Facades\Route;
use Webkul\Customer\Models\Customer;
use Webkul\Wallet\Enums\WalletStatus;
use Webkul\Wallet\Enums\WalletTransactionDirection;
use Webkul\Wallet\Enums\WalletTransactionStatus;
use Webkul\Wallet\Enums\WalletTransactionType;
use Webkul\Wallet\Models\Wallet;
use Webkul\Wallet\Models\WalletTransaction;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('registers admin wallet routes for customers and sales', function () {
    expect(Route::has('admin.customers.wallets.index'))->toBeTrue();
    expect(Route::has('admin.customers.wallets.view'))->toBeTrue();
    expect(Route::has('admin.sales.wallet_transactions.index'))->toBeTrue();
});

it('renders the wallet admin management pages for customers and sales', function () {
    $this->loginAsAdmin();

    $customer = Customer::factory()->create();

    $wallet = Wallet::query()->create([
        'customer_id' => $customer->id,
        'currency' => core()->getBaseCurrencyCode(),
        'balance' => 150,
        'available_balance' => 125,
        'held_balance' => 25,
        'status' => WalletStatus::ACTIVE,
    ]);

    $transaction = WalletTransaction::query()->create([
        'wallet_id' => $wallet->id,
        'customer_id' => $customer->id,
        'type' => WalletTransactionType::ADJUSTMENT,
        'direction' => WalletTransactionDirection::CREDIT,
        'amount' => 25,
        'balance_before' => 125,
        'balance_after' => 150,
        'status' => WalletTransactionStatus::COMPLETED,
        'source' => 'admin.wallet.adjustment',
        'description' => 'Admin balance adjustment',
        'entry_key' => 'wallet-management-test-'.$wallet->id,
    ]);

    get(route('admin.customers.wallets.index'))
        ->assertOk()
        ->assertSee('Wallets');

    get(route('admin.customers.wallets.view', $wallet->id))
        ->assertOk()
        ->assertSee('Wallet Summary')
        ->assertSee('Manual Adjustment')
        ->assertSee((string) $wallet->id);

    get(route('admin.sales.wallet_transactions.index'))
        ->assertOk()
        ->assertSee('Wallet Transactions')
        ->assertSee((string) $transaction->id)
        ->assertSee('Admin balance adjustment');
});

it('renders wallet admin links in the sidebar menu tree', function () {
    $this->loginAsAdmin();

    $this->view('admin::components.layouts.sidebar.desktop.index')
        ->assertSee(route('admin.customers.wallets.index'), false)
        ->assertSee(route('admin.sales.wallet_transactions.index'), false)
        ->assertSee('Wallets')
        ->assertSee('Wallet Transactions');
});

it('renders wallet admin controls in the wallets index page', function () {
    $this->loginAsAdmin();

    $customer = Customer::factory()->create();

    $wallet = Wallet::query()->create([
        'customer_id' => $customer->id,
        'currency' => core()->getBaseCurrencyCode(),
        'balance' => 10,
        'available_balance' => 10,
        'held_balance' => 0,
        'status' => WalletStatus::ACTIVE,
    ]);

    get(route('admin.customers.wallets.index'))
        ->assertOk()
        ->assertSee('Add Credit')
        ->assertSee('Debit')
        ->assertSee('View Wallet')
        ->assertSee('Transactions')
        ->assertSee((string) $wallet->id);
});

it('lets admin create a wallet and credit it from wallet controls', function () {
    $this->loginAsAdmin();

    $customer = Customer::factory()->create();

    expect(Wallet::query()->where('customer_id', $customer->id)->exists())->toBeFalse();

    post(route('admin.customers.wallets.manage'), [
        'customer_id' => $customer->id,
        'action_type' => 'credit',
        'amount' => 50,
        'description' => 'Initial wallet credit',
    ])
        ->assertRedirect();

    $wallet = Wallet::query()->where('customer_id', $customer->id)->first();

    expect($wallet)->not->toBeNull();
    expect((float) $wallet->balance)->toBe(50.0);
    expect((float) $wallet->available_balance)->toBe(50.0);

    $transaction = WalletTransaction::query()->where('wallet_id', $wallet->id)->latest('id')->first();

    expect($transaction)->not->toBeNull();
    expect($transaction->description)->toBe('Initial wallet credit');
});

it('lets admin debit an existing wallet from wallet controls', function () {
    $this->loginAsAdmin();

    $customer = Customer::factory()->create();

    $wallet = Wallet::query()->create([
        'customer_id' => $customer->id,
        'currency' => core()->getBaseCurrencyCode(),
        'balance' => 80,
        'available_balance' => 80,
        'held_balance' => 0,
        'status' => WalletStatus::ACTIVE,
    ]);

    post(route('admin.customers.wallets.manage'), [
        'customer_id' => $customer->id,
        'action_type' => 'debit',
        'amount' => 25,
        'description' => 'Wallet debit test',
    ])
        ->assertRedirect();

    $wallet->refresh();

    expect((float) $wallet->balance)->toBe(55.0);
    expect((float) $wallet->available_balance)->toBe(55.0);
});
