<x-admin::layouts>
    <x-slot:title>
        Payments
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            Payments
        </p>
    </div>

    <div class="mt-6 rounded box-shadow bg-white dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="border-b dark:border-gray-800">
                    <tr class="[&>*]:px-4 [&>*]:py-3 text-sm text-gray-600 dark:text-gray-300">
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Method</th>
                        <th>Purpose</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($payments as $payment)
                        <tr class="border-b align-top dark:border-gray-800 [&>*]:px-4 [&>*]:py-3">
                            <td>#{{ $payment->id }}</td>
                            <td>{{ $payment->customer?->name ?? '-' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method->value)) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payment->purpose->value)) }}</td>
                            <td>{{ core()->formatBasePrice((float) $payment->amount) }}</td>
                            <td>
                                <span class="label-{{ $payment->status->value === 'paid' ? 'active' : ($payment->status->value === 'rejected' ? 'canceled' : 'pending') }}">
                                    {{ ucfirst(str_replace('_', ' ', $payment->status->value)) }}
                                </span>
                            </td>
                            <td>{{ $payment->created_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                <a
                                    href="{{ route('admin.sales.payments.view', $payment->id) }}"
                                    class="text-blue-600"
                                >
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">
                                No payments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $payments->links() }}
    </div>
</x-admin::layouts>
