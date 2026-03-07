<x-admin::layouts>
    <x-slot:title>
        Wallet Transactions
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            Wallet Transactions
        </p>
    </div>

    <div class="mt-6 rounded box-shadow bg-white dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="border-b dark:border-gray-800">
                    <tr class="[&>*]:px-4 [&>*]:py-3 text-sm text-gray-600 dark:text-gray-300">
                        <th>ID</th>
                        <th>Wallet</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Direction</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Description</th>
                        <th>Created</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr class="border-b align-top dark:border-gray-800 [&>*]:px-4 [&>*]:py-3">
                            <td>#{{ $transaction->id }}</td>
                            <td>
                                <a
                                    href="{{ route('admin.customers.wallets.view', $transaction->wallet_id) }}"
                                    class="text-blue-600"
                                >
                                    #{{ $transaction->wallet_id }}
                                </a>
                            </td>
                            <td>{{ $transaction->customer?->name ?? '-' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $transaction->type->value)) }}</td>
                            <td>{{ ucfirst($transaction->direction->value) }}</td>
                            <td>{{ core()->formatBasePrice((float) $transaction->amount) }}</td>
                            <td>{{ ucfirst($transaction->status->value) }}</td>
                            <td>{{ $transaction->description ?: '-' }}</td>
                            <td>{{ $transaction->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500">
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
