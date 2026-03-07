<x-admin::layouts>
    <x-slot:title>
        Payment #{{ $payment->id }}
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            Payment #{{ $payment->id }}
        </p>

        <a href="{{ route('admin.sales.payments.index') }}" class="transparent-button">
            Back
        </a>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-2">
        <div class="rounded box-shadow bg-white dark:bg-gray-900">
            <p class="border-b p-4 font-semibold text-gray-800 dark:border-gray-800 dark:text-white">Payment Details</p>

            <div class="space-y-3 p-4">
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Customer</span>
                    <span>{{ $payment->customer?->name ?? '-' }}</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Method</span>
                    <span>{{ ucfirst(str_replace('_', ' ', $payment->payment_method->value)) }}</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Purpose</span>
                    <span>{{ ucfirst(str_replace('_', ' ', $payment->purpose->value)) }}</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Amount</span>
                    <span>{{ core()->formatBasePrice((float) $payment->amount) }}</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Status</span>
                    <span>{{ ucfirst(str_replace('_', ' ', $payment->status->value)) }}</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Settlement Key</span>
                    <span class="break-all text-right">{{ $payment->settlement_key }}</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">External Reference</span>
                    <span>{{ $payment->external_reference ?: '-' }}</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Created At</span>
                    <span>{{ $payment->created_at?->format('Y-m-d H:i') }}</span>
                </div>
            </div>
        </div>

        <div class="rounded box-shadow bg-white dark:bg-gray-900">
            <p class="border-b p-4 font-semibold text-gray-800 dark:border-gray-800 dark:text-white">Review & Linkage</p>

            <div class="space-y-3 p-4">
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Reviewed By</span>
                    <span>{{ $payment->reviewer?->name ?? '-' }}</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Reviewed At</span>
                    <span>{{ $payment->reviewed_at?->format('Y-m-d H:i') ?: '-' }}</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Paid At</span>
                    <span>{{ $payment->paid_at?->format('Y-m-d H:i') ?: '-' }}</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Fulfilled At</span>
                    <span>{{ $payment->fulfilled_at?->format('Y-m-d H:i') ?: '-' }}</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Linked Entity</span>
                    <span>{{ class_basename($payment->payable_type ?: '') ?: '-' }}</span>
                </div>

                @if ($payment->payable_type === \Webkul\Sales\Models\Order::class && $payment->payable_id)
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Order</span>
                        <a href="{{ route('admin.sales.orders.view', $payment->payable_id) }}" class="text-blue-600">
                            View order #{{ $payment->payable_id }}
                        </a>
                    </div>
                @endif

                @if ($payment->payment_method->value === 'banktransfer')
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Bank Transfer Review</span>
                        <a
                            href="{{ route('admin.sales.bank-transfers.index') }}"
                            class="text-blue-600"
                        >
                            Open bank transfer queue
                        </a>
                    </div>
                @endif

                @if ($payment->notes)
                    <div>
                        <p class="text-gray-500">Notes</p>
                        <p class="mt-2 rounded bg-gray-50 p-3 text-sm dark:bg-gray-800">{{ $payment->notes }}</p>
                    </div>
                @endif

                @if ($payment->admin_notes)
                    <div>
                        <p class="text-gray-500">Admin Notes</p>
                        <p class="mt-2 rounded bg-gray-50 p-3 text-sm dark:bg-gray-800">{{ $payment->admin_notes }}</p>
                    </div>
                @endif

                @if ($payment->rejection_reason)
                    <div>
                        <p class="text-gray-500">Rejection Reason</p>
                        <p class="mt-2 rounded bg-rose-50 p-3 text-sm text-rose-700 dark:bg-rose-950/20">{{ $payment->rejection_reason }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin::layouts>
