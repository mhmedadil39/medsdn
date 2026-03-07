<x-shop::layouts.account>
    <x-slot:title>
        Wallet
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="mx-4 flex-auto max-md:mx-6 max-sm:mx-4">
        <div class="mb-8 flex items-center justify-between gap-4 max-sm:mb-5 max-sm:flex-col max-sm:items-start">
            <div class="max-md:flex max-md:items-center">
                <a
                    class="grid md:hidden"
                    href="{{ route('shop.customers.account.index') }}"
                >
                    <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
                </a>

                <h2 class="text-2xl font-medium max-sm:text-base ltr:ml-2.5 md:ltr:ml-0 rtl:mr-2.5 md:rtl:mr-0">
                    Wallet
                </h2>
            </div>

            <a
                href="{{ route('shop.customers.account.wallet.topup') }}"
                class="primary-button"
            >
                Top up wallet
            </a>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <p class="text-sm font-medium text-zinc-500">Current Balance</p>
                <p class="mt-3 text-2xl font-semibold text-zinc-900">{{ core()->formatBasePrice((float) $wallet->balance) }}</p>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <p class="text-sm font-medium text-zinc-500">Available Balance</p>
                <p class="mt-3 text-2xl font-semibold text-zinc-900">{{ core()->formatBasePrice((float) $wallet->available_balance) }}</p>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <p class="text-sm font-medium text-zinc-500">Held Balance</p>
                <p class="mt-3 text-2xl font-semibold text-zinc-900">{{ core()->formatBasePrice((float) $wallet->held_balance) }}</p>
            </div>
        </div>

        <div class="mt-8 grid gap-6 xl:grid-cols-2">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Wallet Transactions</h3>
                    <span class="text-sm text-zinc-500">{{ $transactions->total() }} records</span>
                </div>

                <div class="space-y-3">
                    @forelse ($transactions as $transaction)
                        <div class="rounded-xl border border-zinc-200 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-medium text-zinc-900">{{ ucfirst(str_replace('_', ' ', $transaction->type->value)) }}</p>
                                    <p class="text-sm text-zinc-500">{{ $transaction->description ?: 'Wallet transaction' }}</p>
                                </div>

                                <div class="text-right">
                                    <p class="font-semibold {{ $transaction->direction->value === 'credit' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $transaction->direction->value === 'credit' ? '+' : '-' }}{{ core()->formatBasePrice((float) $transaction->amount) }}
                                    </p>
                                    <p class="text-xs text-zinc-500">{{ $transaction->created_at?->format('Y-m-d H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500">No wallet transactions yet.</p>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $transactions->links() }}
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Payments</h3>
                    <span class="text-sm text-zinc-500">{{ $payments->total() }} records</span>
                </div>

                <div class="space-y-3">
                    @forelse ($payments as $payment)
                        <div class="rounded-xl border border-zinc-200 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-medium text-zinc-900">{{ ucfirst(str_replace('_', ' ', $payment->purpose->value)) }}</p>
                                    <p class="text-sm text-zinc-500">{{ ucfirst(str_replace('_', ' ', $payment->status->value)) }}</p>
                                </div>

                                <div class="text-right">
                                    <p class="font-semibold text-zinc-900">{{ core()->formatBasePrice((float) $payment->amount) }}</p>
                                    <p class="text-xs text-zinc-500">{{ $payment->created_at?->format('Y-m-d H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500">No payment records yet.</p>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $payments->links() }}
                </div>
            </div>
        </div>
    </div>
</x-shop::layouts.account>
