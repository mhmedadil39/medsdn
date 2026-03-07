<x-admin::layouts>
    <x-slot:title>
        Wallet #{{ $wallet->id }}
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            Wallet #{{ $wallet->id }}
        </p>

        <a href="{{ route('admin.customers.wallets.index') }}" class="transparent-button">
            Back
        </a>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="rounded box-shadow bg-white dark:bg-gray-900">
            <p class="border-b p-4 font-semibold text-gray-800 dark:border-gray-800 dark:text-white">Wallet Summary</p>

            <div class="space-y-3 p-4">
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Customer</span>
                    <span>{{ $wallet->customer?->name ?? '-' }}</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Currency</span>
                    <span>{{ $wallet->currency }}</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Balance</span>
                    <span>{{ core()->formatBasePrice((float) $wallet->balance) }}</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Available</span>
                    <span>{{ core()->formatBasePrice((float) $wallet->available_balance) }}</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Held</span>
                    <span>{{ core()->formatBasePrice((float) $wallet->held_balance) }}</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Status</span>
                    <span>{{ ucfirst($wallet->status->value) }}</span>
                </div>
            </div>
        </div>

        <div class="rounded box-shadow bg-white dark:bg-gray-900">
            <p class="border-b p-4 font-semibold text-gray-800 dark:border-gray-800 dark:text-white">Manual Adjustment</p>

            <x-admin::form
                method="POST"
                :action="route('admin.customers.wallets.adjust', $wallet->id)"
                class="space-y-4 p-4"
            >
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        Amount
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="number"
                        name="amount"
                        step="0.01"
                        rules="required"
                    />

                    <p class="mt-1 text-xs text-gray-500">Use a negative value to debit the wallet.</p>
                    <x-admin::form.control-group.error control-name="amount" />
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        Description
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="textarea"
                        name="description"
                        rows="4"
                        rules="required"
                    />

                    <x-admin::form.control-group.error control-name="description" />
                </x-admin::form.control-group>

                <button type="submit" class="primary-button">
                    Apply adjustment
                </button>
            </x-admin::form>
        </div>
    </div>

    <div class="mt-4 rounded box-shadow bg-white dark:bg-gray-900">
        <p class="border-b p-4 font-semibold text-gray-800 dark:border-gray-800 dark:text-white">Transactions</p>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="border-b dark:border-gray-800">
                    <tr class="[&>*]:px-4 [&>*]:py-3 text-sm text-gray-600 dark:text-gray-300">
                        <th>ID</th>
                        <th>Type</th>
                        <th>Direction</th>
                        <th>Amount</th>
                        <th>Balance Before</th>
                        <th>Balance After</th>
                        <th>Description</th>
                        <th>Created</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr class="border-b align-top dark:border-gray-800 [&>*]:px-4 [&>*]:py-3">
                            <td>#{{ $transaction->id }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $transaction->type->value)) }}</td>
                            <td>{{ ucfirst($transaction->direction->value) }}</td>
                            <td>{{ core()->formatBasePrice((float) $transaction->amount) }}</td>
                            <td>{{ core()->formatBasePrice((float) $transaction->balance_before) }}</td>
                            <td>{{ core()->formatBasePrice((float) $transaction->balance_after) }}</td>
                            <td>{{ $transaction->description ?: '-' }}</td>
                            <td>{{ $transaction->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">
                                No wallet transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $transactions->links() }}
        </div>
    </div>
</x-admin::layouts>
