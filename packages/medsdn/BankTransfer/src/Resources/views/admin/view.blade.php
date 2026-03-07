<x-admin::layouts>
    <x-slot:title>
        @lang('banktransfer::app.admin.view.title')
    </x-slot>

    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
        <p class="text-xl text-gray-800 dark:text-white font-bold">
            @lang('banktransfer::app.admin.view.title') #{{ $payment->id }}
        </p>

        <div class="flex gap-x-2.5 items-center">
            <a href="{{ route('admin.sales.bank-transfers.index') }}" class="transparent-button">
                @lang('admin::app.account.edit.back-btn')
            </a>
        </div>
    </div>

    <div class="flex gap-2.5 mt-3.5 max-xl:flex-wrap">
        <!-- Order Information -->
        <div class="flex flex-col gap-2 flex-1 max-xl:flex-auto">
            <div class="bg-white dark:bg-gray-900 rounded box-shadow">
                <p class="text-gray-800 dark:text-white font-semibold mb-4 p-4 border-b dark:border-gray-800">
                    @lang('banktransfer::app.admin.view.order-info')
                </p>

                <div class="p-4 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">@lang('banktransfer::app.admin.view.order-number'):</span>
                        <a href="{{ route('admin.sales.orders.view', $payment->order_id) }}" class="text-blue-600">
                            #{{ $payment->order->increment_id }}
                        </a>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">@lang('banktransfer::app.admin.view.customer'):</span>
                        <span>{{ $payment->customer?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">@lang('banktransfer::app.admin.view.order-total'):</span>
                        <span>{{ core()->formatBasePrice($payment->order->base_grand_total) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">@lang('banktransfer::app.admin.view.order-date'):</span>
                        <span>{{ $payment->order->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="bg-white dark:bg-gray-900 rounded box-shadow">
                <p class="text-gray-800 dark:text-white font-semibold mb-4 p-4 border-b dark:border-gray-800">
                    @lang('banktransfer::app.admin.view.payment-info')
                </p>

                <div class="p-4 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">@lang('banktransfer::app.admin.view.transaction-ref'):</span>
                        <span>{{ $payment->transaction_reference ?: '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">@lang('banktransfer::app.admin.view.upload-date'):</span>
                        <span>{{ $payment->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">@lang('banktransfer::app.admin.view.status'):</span>
                        <span class="label-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-300">@lang('banktransfer::app.admin.view.payment-proof'):</span>
                        <a href="{{ route('admin.sales.bank-transfers.file', $payment->id) }}" class="text-blue-600" target="_blank">
                            @lang('banktransfer::app.admin.view.download')
                        </a>
                    </div>
                </div>
            </div>

            @if($payment->reviewed_at)
            <!-- Review Information -->
            <div class="bg-white dark:bg-gray-900 rounded box-shadow">
                <p class="text-gray-800 dark:text-white font-semibold mb-4 p-4 border-b dark:border-gray-800">
                    @lang('banktransfer::app.admin.view.review-info')
                </p>

                <div class="p-4 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">@lang('banktransfer::app.admin.view.reviewed-by'):</span>
                        <span>{{ $payment->reviewer->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">@lang('banktransfer::app.admin.view.reviewed-at'):</span>
                        <span>{{ $payment->reviewed_at->format('Y-m-d H:i') }}</span>
                    </div>
                    @if($payment->admin_note)
                    <div>
                        <span class="text-gray-600 dark:text-gray-300">@lang('banktransfer::app.admin.view.admin-note'):</span>
                        <p class="mt-2 p-3 bg-gray-50 dark:bg-gray-800 rounded">{{ $payment->admin_note }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Actions -->
        @if($payment->isPending())
        <div class="flex flex-col gap-2 w-[360px] max-w-full max-sm:w-full">
            <x-admin::accordion>
                <x-slot:header>
                    <p class="text-gray-600 dark:text-gray-300 text-base font-semibold">
                        @lang('admin::app.sales.orders.view.actions')
                    </p>
                </x-slot>

                <x-slot:content>
                    <div class="flex flex-col gap-4">
                        <button
                            type="button"
                            class="primary-button"
                            @click="$refs.approveModal.open()"
                        >
                            @lang('banktransfer::app.admin.view.approve')
                        </button>

                        <button
                            type="button"
                            class="secondary-button"
                            @click="$refs.rejectModal.open()"
                        >
                            @lang('banktransfer::app.admin.view.reject')
                        </button>
                    </div>
                </x-slot>
            </x-admin::accordion>
        </div>

        <!-- Approve Modal -->
        <x-admin::form method="POST" :action="route('admin.sales.bank-transfers.approve', $payment->id)">
            <x-admin::modal ref="approveModal">
                <x-slot:header>
                    @lang('banktransfer::app.admin.view.approve')
                </x-slot>

                <x-slot:content>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('banktransfer::app.admin.view.approval-note')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            name="admin_note"
                            rows="4"
                        />
                    </x-admin::form.control-group>
                </x-slot>

                <x-slot:footer>
                    <button type="submit" class="primary-button">
                        @lang('banktransfer::app.admin.view.approve')
                    </button>
                </x-slot>
            </x-admin::modal>
        </x-admin::form>

        <!-- Reject Modal -->
        <x-admin::form method="POST" :action="route('admin.sales.bank-transfers.reject', $payment->id)">
            <x-admin::modal ref="rejectModal">
                <x-slot:header>
                    @lang('banktransfer::app.admin.view.reject')
                </x-slot>

                <x-slot:content>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('banktransfer::app.admin.view.rejection-note')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            name="admin_note"
                            rules="required"
                            rows="4"
                        />

                        <x-admin::form.control-group.error control-name="admin_note" />
                    </x-admin::form.control-group>
                </x-slot>

                <x-slot:footer>
                    <button type="submit" class="primary-button">
                        @lang('banktransfer::app.admin.view.reject')
                    </button>
                </x-slot>
            </x-admin::modal>
        </x-admin::form>
        @endif
    </div>
</x-admin::layouts>
